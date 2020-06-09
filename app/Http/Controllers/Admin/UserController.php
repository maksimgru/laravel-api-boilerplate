<?php

namespace App\Http\Controllers\Admin;

use App\Constants\RoleConstants;
use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\GetOwnBusinessUserRequest;
use App\Http\Requests\User\GetOwnBusinessWorkerUserRequest;
use App\Http\Requests\User\GetOwnWorkerUserRequest;
use App\Http\Requests\User\GetActiveTouristUserRequest;
use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Tasks\User\ListUsersTask;
use App\Http\Tasks\User\RegisterUserTask;
use App\Http\Tasks\User\UpdateUserTask;
use App\Models\Role;
use App\Models\User;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use Illuminate\Database\QueryException;
use App\Http\Requests\Request;
use InvalidArgumentException;
use LogicException;
use Prettus\Repository\Exceptions\RepositoryException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
    public static $model = User::class;

    /**
     * Get List Users
     *
     * @SWG\Get(
     *  path="/admin/users",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active | primary_role_id | manager_id | business_id). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|properties->phone|is_active|first_name|last_name|primary_role_id|manager_id|business_id|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentManager|parentBusiness|ownBusinesses|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentManager|parentBusiness|ownBusinesses|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": 4,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "email": "manager@example.com",
     *                         "isActive": false,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "33", "name": "manager", "description": "Manager Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": null,
     *                     "manager": null,
     *                     "business": null
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/users?page=1", "next": "/api/admin/users?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask    $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function index(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        return $this->response->paginator(
            $listUsersTask->run($paginationRequest->input()),
            $this->getTransformer()
        );
    }

    /**
     * Get Single User
     *
     * @SWG\Get(
     *  path="/admin/users/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": false,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "manager": {
     *                     "id": 4,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetUserRequest $getUserRequest
     * @param int            $userId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSingleUser(
        GetUserRequest $getUserRequest,
        int $userId
    ): Response {
        return $this->get($userId);
    }

    /**
     * Create User
     *
     * @SWG\Post(
     *  path="/admin/users",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="required|min:3|max:255"),
     *        @SWG\Property(property="password", type="string", description="required|min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="primaryRole", type="integer", description="required|exists:roles,id"),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *        @SWG\Property(property="managerId", type="integer", description="nullable|exists:users,id"),
     *        @SWG\Property(property="businessId", type="integer", description="nullable|exists:users,id"),
     *        @SWG\Property(property="isActive", type="boolean", description=""),
     *        @SWG\Property(property="balance", type="number", example=12.99, description="0.00"),
     *        @SWG\Property(property="commission", type="numeric", example=2.5, description="0.00"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=201, description="Created",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "manager": {
     *                     "id": 4,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request          $request
     * @param RegisterUserTask $registerUserTask
     *
     * @return Response
     * @throws AccessDeniedHttpException
     * @throws StoreResourceFailedException
     * @throws InvalidArgumentException
     */
    public function postUser(
        Request $request,
        RegisterUserTask $registerUserTask
    ): Response {
        return $this->response
            ->item(
                $registerUserTask->run($request->getSanitizedInputs(), static::$model),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_CREATED)
        ;
    }

    /**
     * Update User. Can update each field separately, even password.
     *
     * @SWG\Post(
     *  path="/admin/users/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="password", type="string", description="min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="primaryRole", type="integer", description="exists:roles,id"),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *        @SWG\Property(property="managerId", type="integer", description="nullable|exists:users,id"),
     *        @SWG\Property(property="businessId", type="integer", description="nullable|exists:users,id"),
     *        @SWG\Property(property="isActive", type="boolean", description=""),
     *        @SWG\Property(property="balance", type="number", example=12.99, description="0.00"),
     *        @SWG\Property(property="commission", type="numeric", example=2.5, description="0.00"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=200, description="Updated",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "manager": {
     *                     "id": 4,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param UpdateUserRequest $updateUserRequest
     * @param UpdateUserTask    $updateUserTask
     * @param int            $userId ID of the resource
     *
     * @return Response
     * @throws LogicException
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     * @throws QueryException
     * @throws UnprocessableEntityHttpException
     * @throws InvalidArgumentException
     * @throws AccessDeniedHttpException
     * @throws ConflictHttpException
     */
    public function updateUser(
        UpdateUserRequest $updateUserRequest,
        UpdateUserTask $updateUserTask,
        int $userId
    ): Response {
        $user = static::$model::findOrFail($userId);

        return $this->response
            ->item(
                $updateUserTask->run($updateUserRequest->getSanitizedInputs(), $user),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_OK)
        ;
    }

    /**
     * Soft Delete User
     *
     * @SWG\Delete(
     *  path="/admin/users/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=204, description="No content"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param DeleteUserRequest $deleteUserRequest
     * @param int               $userId ID of the resource
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function deleteUser(
        DeleteUserRequest $deleteUserRequest,
        int $userId
    ): Response {
        return $this->delete($userId);
    }

    /**
     * Force Delete User
     *
     * @SWG\Delete(
     *  path="/admin/users/force/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=204, description="No content"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param DeleteUserRequest $deleteUserRequest
     * @param int               $userId ID of the resource
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function forceDeleteUser(
        DeleteUserRequest $deleteUserRequest,
        int $userId
    ): Response {
        return $this->forceDelete($userId);
    }

    /**
     * Restore User
     *
     * @SWG\Delete(
     *  path="/admin/users/restore/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success"),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param DeleteUserRequest $deleteUserRequest
     * @param int               $userId ID of the resource
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function restoreUser(
        DeleteUserRequest $deleteUserRequest,
        int $userId
    ): Response {
        return $this->restore($userId);
    }

    /**
     * Get List Soft Deleted Users
     *
     * @SWG\Get(
     *  path="/admin/users/trash",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active | primary_role_id | manager_id | business_id). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|properties->phone|is_active|first_name|last_name|primary_role_id|manager_id|business_id|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentManager|parentBusiness|ownBusinesses|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentManager|parentBusiness|ownBusinesses|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": 4,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "email": "manager@example.com",
     *                         "isActive": false,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "33", "name": "manager", "description": "Manager Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": null,
     *                     "manager": null,
     *                     "business": null
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/users?page=1", "next": "/api/admin/users?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask    $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getSoftDeletedUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        return $this->response->paginator(
            $listUsersTask->run(array_merge($paginationRequest->input(), ['only_trashed' => true])),
            $this->getTransformer()
        );
    }

    /**
     * Get Single Soft Deleted User
     *
     * @SWG\Get(
     *  path="/admin/users/trash/{user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="user_id",
     *     description="User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": false,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "manager": {
     *                     "id": 4,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": false,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetUserRequest $getUserRequest
     * @param int            $userId
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function getSingleSoftDeletedUser(
        GetUserRequest $getUserRequest,
        int $userId
    ): Response {
        return $this->getSoftDeleted($userId);
    }

    /**
     * Get Own Business Users
     *
     * @SWG\Get(
     *  path="/admin/manager/businesses",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentManager|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?with=parentManager;ownWorkers",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentManager|ownWorkers|roles|primaryRole). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": null,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "email": "manager@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": null,
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": null,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "email": "manager@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": null,
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/manager/businesses?page=1", "next": "/api/admin/manager/businesses?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getOwnBusinessUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS);
        $input['manager_id'] = $this->user()->id;

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Own Business's Worker Users
     *
     * @SWG\Get(
     *  path="/admin/manager/workers",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "4", "name": "worker", "description": "Worker Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": null,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "email": "manager@example.com",
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": null,
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "4", "name": "worker", "description": "Worker Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": 4,
     *                     "businessId": null,
     *                     "manager": {
     *                         "id": 4,
     *                         "firstName": "Manager",
     *                         "lastName": "Power",
     *                         "username": "manager",
     *                         "email": "manager@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                         "managerId": null,
     *                         "businessId": null
     *                     },
     *                     "business": null,
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/manager/workers?page=1", "next": "/api/admin/manager/workers?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getOwnBusinessWorkerUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
        $input['business_id'] = $this->user()->ownBusinesses->pluck('id')->toArray();

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Single Own Business User
     *
     * @SWG\Get(
     *  path="/admin/manager/businesses/{business_user_id}",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="business_user_id",
     *     description="Business User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "business", "description": "Business Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "managerId": 4,
     *                 "businessId": null,
     *                 "manager": {
     *                     "id": 4,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": null,
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetOwnBusinessUserRequest $getOwnBusinessUserRequest
     * @param int                       $userId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSingleOwnBusinessUser(
        GetOwnBusinessUserRequest $getOwnBusinessUserRequest,
        int $userId
    ): Response {
        return $this->get($userId);
    }

    /**
     * Get Single Own Business's Worker User
     *
     * @SWG\Get(
     *  path="/admin/manager/workers/{worker_user_id}",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="worker_user_id",
     *     description="Worker User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "worker", "description": "worker Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "managerId": null,
     *                 "businessId": 4,
     *                 "manager": null,
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "business",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "email": "manager@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetOwnBusinessWorkerUserRequest $getOwnBusinessWorkerUserRequest
     * @param int                             $userId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSingleOwnBusinessWorkerUser(
        GetOwnBusinessWorkerUserRequest $getOwnBusinessWorkerUserRequest,
        int $userId
    ): Response {
        return $this->get($userId);
    }

    /**
     * Create Business User By Manager User
     *
     * @SWG\Post(
     *  path="/admin/manager/businesses",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="required|min:3|max:255"),
     *        @SWG\Property(property="password", type="string", description="required|min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="isActive", type="boolean", description=""),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *        @SWG\Property(property="balance", type="number", example=12.99, description="0.00"),
     *        @SWG\Property(property="commission", type="numeric", example=2.5, description="0.00"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=201, description="Created",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "Business",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "business@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {
     *                     "id": "3",
     *                     "name": "business",
     *                     "description": "Business Users"
     *                 },
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "manager": {
     *                     "id": 2,
     *                     "firstName": "Manager",
     *                     "lastName": "Power",
     *                     "username": "manager",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "email": "manager@example.com",
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "manager", "description": "Manager Users"},
     *                     "managerId": null,
     *                     "businessId": null
     *                 },
     *                 "business": null,
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request          $request
     * @param RegisterUserTask $registerUserTask
     *
     * @return Response
     * @throws LogicException
     * @throws QueryException
     * @throws StoreResourceFailedException
     * @throws InvalidArgumentException
     */
    public function postOwnBusinessUser(
        Request $request,
        RegisterUserTask $registerUserTask
    ): Response {
        $request->request->set('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS));
        $request->request->set('manager_id', $this->user()->id);

        return $this->response
            ->item(
                $registerUserTask->run($request->getSanitizedInputs(), static::$model),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_CREATED)
        ;
    }

    /**
     * Get Visted Tourist Users
     *
     * @SWG\Get(
     *  path="/admin/manager/tourists/visited",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/manager/tourists/referrals?page=1", "next": "/api/admin/manager/tourists/referrals?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getManagerVisitedTouristUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);
        $input['id'] = $this->user()->getVisitedTouristIds();

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Referral Tourist Users
     *
     * @SWG\Get(
     *  path="/admin/manager/tourists/referrals",
     *  tags={"Admin/Manager/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/manager/tourists/referrals?page=1", "next": "/api/admin/manager/tourists/referrals?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getManagerReferralTouristUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);
        $input['business_id'] = $this->user()->getOwnBusinessesIds();

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Own Worker Users
     *
     * @SWG\Get(
     *  path="/admin/business/workers",
     *  tags={"Admin/Business/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "worker", "description": "Worker Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "worker", "description": "Worker Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/business/workers?page=1", "next": "/api/admin/business/workers?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getOwnWorkerUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
        $input['business_id'] = $this->user()->id;

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Single Own Worker User
     *
     * @SWG\Get(
     *  path="/admin/business/workers/{worker_user_id}",
     *  tags={"Admin/Business/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="worker_user_id",
     *     description="Worker User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "33", "name": "worker", "description": "Worker Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "managerId": null,
     *                 "businessId": 4,
     *                 "manager": null,
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetOwnWorkerUserRequest $getOwnWorkerUserRequest
     * @param int                     $userId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSingleOwnWorkerUser(
        GetOwnWorkerUserRequest $getOwnWorkerUserRequest,
        int $userId
    ): Response {
        return $this->get($userId);
    }

    /**
     * Create Worker User By Business User
     *
     * @SWG\Post(
     *  path="/admin/business/workers",
     *  tags={"Admin/Business/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="required|min:3|max:255"),
     *        @SWG\Property(property="password", type="string", description="required|min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="isActive", type="boolean", description=""),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *        @SWG\Property(property="balance", type="number", example=12.99, description="0.00"),
     *        @SWG\Property(property="commission", type="numeric", example=2.5, description="0.00"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=201, description="Created",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "Worker",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "worker@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {
     *                     "id": "4",
     *                     "name": "worker",
     *                     "description": "Worker Users"
     *                 },
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "3", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *                 "manager": null,
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request          $request
     * @param RegisterUserTask $registerUserTask
     *
     * @return Response
     * @throws StoreResourceFailedException
     * @throws InvalidArgumentException
     */
    public function postOwnWorkerUser(
        Request $request,
        RegisterUserTask $registerUserTask
    ): Response {
        $request->request->set('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_WORKER));
        $request->request->set('business_id', $this->user()->id);

        return $this->response
            ->item(
                $registerUserTask->run($request->getSanitizedInputs(), static::$model),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_CREATED)
        ;
    }

    /**
     * Get Visited Tourist Users
     *
     * @SWG\Get(
     *  path="/admin/business/tourists/visited",
     *  tags={"Admin/Business/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/business/tourists/referrals?page=1", "next": "/api/admin/business/tourists/referrals?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getBusinessVisitedTouristUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);
        $input['id'] = $this->user()->getVisitedTouristIds();

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Referral Tourist Users
     *
     * @SWG\Get(
     *  path="/admin/business/tourists/referrals",
     *  tags={"Admin/Business/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/business/tourists/referrals?page=1", "next": "/api/admin/business/tourists/referrals?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getBusinessReferralTouristUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);
        $input['business_id'] = $this->user()->id;

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Active Tourist Users. Availabel for admin, managers, businesses, workers
     *
     * @SWG\Get(
     *  path="/admin/users/tourists",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="page",
     *     description="Page number of pagination. Example: http://localhost/?page=2",
     *     default=1,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="per_page",
     *     description="Number of items per-page in pagination. Example: http://localhost/?per_page=5",
     *     default=10,
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search",
     *     description="Searched value. Request parameter that will be used to filter the query in the repository. Example: http://localhost/?search=lorem",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_fields",
     *     description="Fields in which research should be carried out. Separated by ';'. Available (first_name | last_name | username | email | properties->phone | is_active). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=username;email | http://localhost/?search=lorem&search_fields=username:like;email:ilike | http://localhost/?search=username:John;email:john@example.com&search_fields=username:ilike;email | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=username;email | http://localhost/?search=username:John;email:john@example.com&search_join=or&search_fields=username:like;email:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|username|email|first_name|last_name|properties->phone|is_active|created_at|updated_at|etc.). Example: http://localhost/?search=lorem&order_by=id",
     *     default="id",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="sorted_by",
     *     description="Sort By Direction (asc|desc). Example: http://localhost/?search=lorem&order_by=id&sorted_by=desc",
     *     default="asc",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="select",
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;username",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object (parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?with=parentManager;parentBusiness",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations (Available: parentBusiness|roles|primaryRole|touristTransactions). Separated by ';'. Example: http://localhost/?withCount=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="skip_cache",
     *     description="Skip Cache Params (1|0). Example: http://localhost/?search=lorem&skip_cache=1",
     *     default="0",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="is_active",
     *     description="Filter tourist users status: Active, Deactive, or Both. By default only active. Both status value can be separate by ';'. Example: http://localhost/?is_active=1;0",
     *     default="1",
     *     in="query",
     *     type="integer",
     *     required=false,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 {
     *                     "id": "33",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 },
     *                 {
     *                     "id": "44",
     *                     "firstName": "User",
     *                     "lastName": "Power",
     *                     "username": "My username",
     *                     "email": "user@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                     "media": {
     *                         "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                     },
     *                     "managerId": null,
     *                     "businessId": 4,
     *                     "manager": null,
     *                     "business": {
     *                         "id": 4,
     *                         "firstName": "Business",
     *                         "lastName": "Power",
     *                         "username": "business",
     *                         "email": "business@example.com",
     *                         "isActive": true,
     *                         "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                         "createdAt": "2019-12-26T17:19:16+00:00",
     *                         "updatedAt": "2019-12-26T23:16:20+00:00",
     *                         "roles": {},
     *                         "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                         "managerId": 2,
     *                         "businessId": null
     *                     },
     *                 }
     *             },
     *             "meta": {
     *                     "pagination": {
     *                     "total": 25, "count": 10,
     *                     "perPage": 10,
     *                     "currentPage": 2,
     *                     "totalPages": 3,
     *                     "links": {"previous": "/api/admin/business/tourists/referrals?page=1", "next": "/api/admin/business/tourists/referrals?page=3"}
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     * )
     *
     * @param PaginationRequest $paginationRequest
     * @param ListUsersTask     $listUsersTask
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws RepositoryException
     * @throws AccessDeniedHttpException
     */
    public function getActiveTouristUsers(
        PaginationRequest $paginationRequest,
        ListUsersTask $listUsersTask
    ): Response {
        $input = $paginationRequest->input();
        $input['primary_role_id'] = Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);


        // Filter tourist users status: Active, Deactive, or Both. By default only active.
        // Both status value can be separate by ";" i.e. /?is_active=1;0
        if (!isset($input['is_active'])) {
            $input['is_active'] = true;
        } else {
            $isActiveCases = array_unique(array_map(
                function ($case) {
                    return (bool) $case;
                },
                explode(';', $input['is_active'])
            ));
            if (\count($isActiveCases) === 1) {
                $input['is_active'] = $isActiveCases[0];
            } else {
                unset($input['is_active']);
            }
        }

        return $this->response->paginator(
            $listUsersTask->run($input),
            $this->getTransformer()
        );
    }

    /**
     * Get Single Tourist User. Availabel for admin, managers, businesses, workers
     *
     * @SWG\Get(
     *  path="/admin/users/tourists/{tourist_user_id}",
     *  tags={"Admin/Users"},
     *
     *  @SWG\Parameter(
     *     name="Authorization",
     *     description="Bearer {token}",
     *     default="Bearer {token}",
     *     in="header",
     *     type="string",
     *     required=true,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="tourist_user_id",
     *     description="Tourist User ID",
     *     default="33",
     *     in="path",
     *     type="integer",
     *     required=true,
     *  ),
     *
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "User",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "user@example.com",
     *                 "isActive": true,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {"id": "5", "name": "tourist", "description": "tourist Users"},
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "managerId": null,
     *                 "businessId": 4,
     *                 "manager": null,
     *                 "business": {
     *                     "id": 4,
     *                     "firstName": "Business",
     *                     "lastName": "Power",
     *                     "username": "business",
     *                     "email": "business@example.com",
     *                     "isActive": true,
     *                     "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0},
     *                     "createdAt": "2019-12-26T17:19:16+00:00",
     *                     "updatedAt": "2019-12-26T23:16:20+00:00",
     *                     "roles": {},
     *                     "primaryRole": {"id": "2", "name": "business", "description": "Business Users"},
     *                     "managerId": 2,
     *                     "businessId": null
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not found"),
     * )
     *
     * @param GetActiveTouristUserRequest $getTouristUserRequest
     * @param int                         $userId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSingleActiveTouristUser(
        GetActiveTouristUserRequest $getTouristUserRequest,
        int $userId
    ): Response {
        return $this->get($userId);
    }
}
