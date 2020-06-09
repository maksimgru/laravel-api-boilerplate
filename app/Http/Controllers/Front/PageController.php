<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginationRequest;
use App\Http\Requests\Page\GetPageRequest;
use App\Http\Tasks\Page\ListPagesTask;
use App\Models\Page;
use Dingo\Api\Http\Response;
use App\Http\Requests\Request;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class PageController
 *
 * @package App\Http\Controllers\Front
 */
class PageController extends Controller
{
    public static $model = Page::class;

    /**
     * Get List Pages.
     *
     * @SWG\Get(
     *  path="/pages",
     *  tags={"Pages"},
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
     *     description="Fields in which research should be carried out. Separated by ';'. Available (title | slug | content). You can use criteria accepted conditions ('like', 'ilike', byDefault '='). Example: http://localhost/?search=lorem&search_fields=field1;field2 | http://localhost/?search=lorem&search_fields=field1:like;field2:ilike | http://localhost/?search=field1:John;field2:Lorem&search_fields=field1:ilike;field2 | etc.",
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
     *     description="Order By field (id|title|slug|content|etc.). Example: http://localhost/?search=lorem&order_by=id",
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
     *     description="Fields that must be returned to the response object. Separated by ';'. Example: http://localhost/?search=lorem&select=id;title",
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
     *                 {
     *                     "id": "1", "title": "Post#1", "slug": "post-1", "content": "Lorem Ipsum",
     *                     "media": {
     *                         "mainImage": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://", "thumb-large": "http://"}},
     *                         "gallery": {{"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://", "thumb-large": "http://"}}},
     *                     },
     *                 },
     *             },
     *             "meta": {
     *                 "pagination": {"total": 25, "count": 10, "perPage": 10, "currentPage": 2, "totalPages": 3, "links": {"previous": "/api/admin/pages?page=1", "next": "/api/admin/pages?page=3"}},
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
     * @param ListPagesTask     $listPagesTask
     *
     * @return Response
     * @throws \InvalidArgumentException
     * @throws RepositoryException
     */
    public function index(
        PaginationRequest $paginationRequest,
        ListPagesTask $listPagesTask
    ): Response {
        return $this->response->paginator(
            $listPagesTask->run($paginationRequest->input()),
            $this->getTransformer()
        );
    }

    /**
     * Get Single Page
     *
     * @SWG\Get(
     *  path="/pages/{page_id}",
     *  tags={"Pages"},
     *
     *  @SWG\Parameter(
     *     name="page_id",
     *     description="Page ID",
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
     *                 "id": "1", "title": "Page#1", "slug": "page-1", "content": "Lorem Ipsum",
     *                 "media": {
     *                     "mainImage": {"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://", "thumb-large": "http://"}},
     *                     "gallery": {{"origin": "http://", "thumbs": {"thumb-small": "http://", "thumb-medium": "http://", "thumb-large": "http://"}}},
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
     * @param GetPageRequest $getPageRequest
     * @param int            $pageId
     *
     * @return Response
     * @throws HttpException
     */
    public function getSinglePage(
        GetPageRequest $getPageRequest,
        int $pageId
    ): Response {
        return $this->get($pageId);
    }
}
