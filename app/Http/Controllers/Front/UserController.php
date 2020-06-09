<?php

namespace App\Http\Controllers\Front;

use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\User\DeleteUserMediaRequest;
use App\Http\Requests\User\RemoveFavoriteVisitPlaceRequest;
use App\Http\Tasks\User\AddFavoriteVisitPlacesTask;
use App\Http\Tasks\User\DeleteUserMediaTask;
use App\Http\Tasks\User\RegisterUserTask;
use App\Http\Tasks\User\RemoveFavoriteVisitPlaceTask;
use App\Http\Tasks\User\UpdateUserTask;
use App\Http\Tasks\VisitPlace\ListVisitPlacesTask;
use App\Models\User;
use App\Transformers\VisitPlacesTransformer;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use App\Http\Requests\Request;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use LogicException;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Front
 */
class UserController extends Controller
{
    public static $model = User::class;

    /**
     * Request to register a new user
     *
     * @SWG\Post(
     *  path="/users/register",
     *  tags={"Users"},
     *
     *  @SWG\Parameter(
     *     name="data",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *        @SWG\Property(property="email", type="string", description="required|email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="required|email|max:255|unique:users"),
     *        @SWG\Property(property="password", type="string", description="required|min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="managerId", type="integer", description=""),
     *        @SWG\Property(property="businessId", type="integer", description=""),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=201, description="Created",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "Tourist",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "tourist@example.com",
     *                 "isActive": false,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0, "favoriteVisitPlaces": {}},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {
     *                     "id": "44",
     *                     "name": "tourist",
     *                     "description": "Tourist Users"
     *                 },
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 }
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request          $request
     * @param RegisterUserTask $registerUserTask
     *
     * @return Response
     * @throws LogicException
     * @throws StoreResourceFailedException
     * @throws InvalidArgumentException
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws ValidationFailedException
     * @throws StoreResourceFailedException
     */
    public function register(
        Request $request,
        RegisterUserTask $registerUserTask
    ): Response {
        return $this->response
            ->item(
                $registerUserTask->run(
                    $request->getSanitizedInputs(
                        [
                            'except' => (new static::$model)::getClosedAttributes()
                        ]
                    ),
                    static::$model
                ),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_CREATED)
        ;
    }

    /**
     * Get the authenticated User.
     *
     * @SWG\Get(
     *  path="/users/me",
     *  tags={"Users"},
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
     *  @SWG\Response(response=200, description="Success",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "Admin",
     *                 "lastName": "Power",
     *                 "username": "My username",
     *                 "email": "admin@example.com",
     *                 "isActive": false,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0, "favoriteVisitPlaces": {1}},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {
     *                     "id": "33",
     *                     "name": "admin",
     *                     "description": "Administrator Users"
     *                 },
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "favoriteVisitPlaces": {
     *                     {
     *                         "id": "1", "title": "Place#1", "slug": "place-1", "description": "Lorem Ipsum", "categoryId": 1, "businessId": 4,
     *                         "properties": {
     *                             "cashBack": 10,
     *                             "phone": "(491) 808-7069",
     *                             "address": "949 Hermann Drives Apt. 457 Kiarraborough, OK 19338-1439",
     *                             "workDatetime": "Mon-Fri, 8:00-19:00",
     *                             "gpsCoordinates": {
     *                                 "lat": -51.743906,
     *                                 "lng": -113.274595
     *                             }
     *                         },
     *                     },
     *                 },
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not Fount"),
     * )
     *
     * @return Response
     * @throws HttpException
     */
    public function getCurrentUser(): Response
    {
        return $this->get($this->auth->user()->getKey());
    }

    /**
     * Update the authenticated User.
     *
     * @SWG\Post(
     *  path="/users/me",
     *  tags={"Users"},
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
     *        @SWG\Property(property="email", type="string", description="email|max:255|unique:users"),
     *        @SWG\Property(property="username", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="password", type="string", description="min:6|max:255"),
     *        @SWG\Property(property="firstName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="lastName", type="string", description="min:3|max:255"),
     *        @SWG\Property(property="birthday", type="string", example="1984-01-31", description="date|dateFormat:Y-m-d"),
     *        @SWG\Property(property="phone", type="string", description=""),
     *        @SWG\Property(property="managerId", type="integer", description=""),
     *        @SWG\Property(property="businessId", type="integer", description=""),
     *        @SWG\Property(property="avatar", type="file", description="image|jpeg, jpg, png"),
     *     )
     *  ),
     *
     *  @SWG\Response(response=200, description="Updated",
     *     examples={
     *         "application/json": {
     *             "data": {
     *                 "id": "33",
     *                 "firstName": "Admin",
     *                 "lastName": "Power",
     *                 "username": "My update username",
     *                 "email": "admin@example.com",
     *                 "isActive": false,
     *                 "properties": {"phone": "+380991234545", "balance": 0.00, "commission": 0, "favoriteVisitPlaces": {1}},
     *                 "createdAt": "2019-12-26T17:19:16+00:00",
     *                 "updatedAt": "2019-12-26T23:16:20+00:00",
     *                 "roles": {},
     *                 "primaryRole": {
     *                     "id": "33",
     *                     "name": "admin",
     *                     "description": "Administrator Users"
     *                 },
     *                 "media": {
     *                     "avatarUrls": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://"}},
     *                 },
     *                 "favoriteVisitPlaces": {
     *                     {
     *                         "id": "1", "title": "Place#1", "slug": "place-1", "description": "Lorem Ipsum", "categoryId": 1, "businessId": 4,
     *                         "properties": {
     *                             "cashBack": 10,
     *                             "phone": "(491) 808-7069",
     *                             "address": "949 Hermann Drives Apt. 457 Kiarraborough, OK 19338-1439",
     *                             "workDatetime": "Mon-Fri, 8:00-19:00",
     *                             "gpsCoordinates": {
     *                                 "lat": -51.743906,
     *                                 "lng": -113.274595
     *                             }
     *                         },
     *                     },
     *                 }
     *             }
     *         }
     *     }
     *  ),
     *  @SWG\Response(response=400, description="Bad Request"),
     *  @SWG\Response(response=401, description="Unauthorized"),
     *  @SWG\Response(response=403, description="Forbidden"),
     *  @SWG\Response(response=404, description="Not Fount"),
     *  @SWG\Response(response=422, description="Unprocessable Entity"),
     * )
     *
     * @param Request        $request
     * @param UpdateUserTask $updateUserTask
     *
     * @return Response
     * @throws HttpException
     * @throws LogicException
     * @throws AccessDeniedHttpException
     * @throws InvalidArgumentException
     * @throws ValidationFailedException
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws StoreResourceFailedException
     */
    public function updateCurrentUser(
        Request $request,
        UpdateUserTask $updateUserTask
    ): Response {
        return $this->response
            ->item(
                $updateUserTask->run(
                    $request->getSanitizedInputs(
                        [
                            'except' => (new static::$model)::getClosedAttributes()
                        ]
                    ),
                    $this->user()
                ),
                $this->getTransformer()
            )
            ->setStatusCode(Response::HTTP_OK)
        ;
    }

    /**
     * Delete Media
     *
     * @SWG\Delete(
     *  path="/users/me/media/{media_id}",
     *  tags={"Users"},
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
     *     name="media_id",
     *     description="Media ID",
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
     * @param DeleteUserMediaRequest $deleteUserMediaRequest
     * @param int                    $mediaId
     *
     * @return Response
     * @throws InvalidArgumentException
     * @throws HttpException
     */
    public function deleteMedia(
        DeleteUserMediaRequest $deleteUserMediaRequest,
        int $mediaId
    ): Response {
        Media::findOrFail($mediaId)->delete();

        return $this->response->noContent()->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
