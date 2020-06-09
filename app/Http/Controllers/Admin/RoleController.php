<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Tasks\Role\ListRolesTask;
use App\Models\Role;
use Illuminate\Http\Response;

/**
 * Class RoleController
 *
 * @package App\Http\Controllers\Admin
 */
class RoleController extends Controller
{
    public static $model = Role::class;

    /**
     * Get List User Roles.
     *
     * @SWG\Get(
     *  path="/admin/roles",
     *  tags={"Admin/Roles"},
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
     *     description="Fields in which research should be carried out. Separated by ';'. Available (id | name | description). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=field1;field2 | http://localhost/?search=lorem&search_fields=field1:like;field2:ilike | http://localhost/?search=field1:John;field2:Lorem&search_fields=field1:ilike;field2 | etc.",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="search_join",
     *     description="Specifies the search method (AND / OR), by default the application searches each parameter with OR. Example: http://localhost/?search=lorem&search_join=and&search_fields=field1;field2 | http://localhost/?search=field1:John;field2:Lorem&search_join=or&search_fields=field1:like;field2:ilike | etc.",
     *     default="or",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="order_by",
     *     description="Order By field (id|name|description.). Example: http://localhost/?search=lorem&order_by=id",
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
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;name",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="with",
     *     description="Add relationship to the response object. Separated by ';'. Example: http://localhost/?with=relationName",
     *     default="",
     *     in="query",
     *     type="string",
     *     required=false,
     *  ),
     *
     *  @SWG\Parameter(
     *     name="withCount",
     *     description="Add subselect queries to count the relations.. Separated by ';'. Example: http://localhost/?withCount=relationName",
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
     *                 {"id": "1", "name": "admin", "description": "Admin users"},
     *                 {"id": "2", "name": "manager", "description": "Manager users"},
     *                 {"id": "3", "name": "business", "description": "Business users"},
     *                 {"id": "4", "name": "worker", "description": "Worker users"},
     *                 {"id": "5", "name": "tourist", "description": "Tourist users"},
     *             },
     *             "meta": {
     *                 "pagination": {"total": 25, "count": 10, "perPage": 10, "currentPage": 1, "totalPages": 3, "links": {"next": "/api/admin/roles?page=2"}},
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
     * @param ListRolesTask     $listRolesTask
     *
     * @return Response
     * @throws HttpException
     */
    public function index(
        PaginationRequest $paginationRequest,
        ListRolesTask $listRolesTask
    ): Response {
        return $this->response->paginator(
            $listRolesTask->run($paginationRequest->input()),
            $this->getTransformer()
        );
    }
}
