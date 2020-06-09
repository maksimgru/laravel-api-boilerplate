<?php

namespace App\Http\Controllers\Web;

use App\Constants\RouteConstants;
use App\Exceptions\Validation\ValidationFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Page\DeletePageMediaRequest;
use App\Http\Requests\Page\GetPageRequest;
use App\Http\Requests\Page\UpdatePageRequest;
use App\Http\Requests\Request;
use App\Http\Tasks\Page\CreatePageTask;
use App\Http\Tasks\Page\UpdatePageTask;
use App\Models\Page;
use App\Transformers\PageTransformer;
use Dingo\Api\Exception\StoreResourceFailedException;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use LogicException;
use Spatie\MediaLibrary\Models\Media;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PagesController extends Controller
{
    /**
     * @return Renderable
     * @throws Exception
     */
    public function index(): Renderable {
        return view('admin-pages.pages', [
            'add_new_model_btn_label'   => trans('Add New Page'),
            'add_new_model_btn_href'    => route(RouteConstants::ROUTE_NAME_WEB_PAGE_VIEW_NEW),
            'list_page_title'           => trans('Pages'),
            'table_load_data_url'       => apiRoute(RouteConstants::ROUTE_NAME_PAGES),
            'default_order'             => ['created_at', 'desc'],
            'url_view_row'              => route(RouteConstants::ROUTE_NAME_WEB_PAGE_VIEW, ['page' => '%id%']),
            'url_delete_row'            => apiRoute(RouteConstants::ROUTE_NAME_DELETE_PAGE, ['page' => '%id%']),
        ]);
    }

    /**
     * @param GetPageRequest $request
     * @param int            $pageId
     *
     * @return Renderable
     * @throws Exception
     */
    public function show(
        GetPageRequest $request,
        int $pageId
    ): Renderable {
        $page = Page::findOrFail($pageId);
        $pageData = (new PageTransformer())->transform($page);

        return view('admin-pages.page',
            [
                'page'     => $pageData,
                'can_edit' => auth()->user()->isAdmin(),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Renderable
     */
    public function showNew(Request $request): Renderable
    {
        return view('admin-pages.page-new',
            [
                'can_create' => auth()->user()->isAdmin(),
            ]
        );
    }

    /**
     * @param Request        $request
     * @param CreatePageTask $createPageTask
     *
     * @return RedirectResponse
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws StoreResourceFailedException
     * @throws ValidationFailedException
     * @throws LogicException
     */
    public function createNew(
        Request $request,
        CreatePageTask $createPageTask
    ): RedirectResponse {
        $page = $createPageTask->run($request->input(), Page::class);

        if ($page->getKey()) {
            return redirect(route(RouteConstants::ROUTE_NAME_WEB_PAGE_VIEW, ['page' => $page->getKey()]));
        }

        return back()
            ->withInput()
            ->withErrors($page->getValidationErrors())
        ;
    }

    /**
     * @param UpdatePageRequest $request
     * @param UpdatePageTask    $updatePageTask
     * @param Page              $page
     *
     * @return RedirectResponse
     * @throws ModelNotFoundException
     * @throws UnprocessableEntityHttpException
     * @throws ConflictHttpException
     * @throws QueryException
     * @throws StoreResourceFailedException
     * @throws ValidationFailedException
     * @throws LogicException
     */
    public function update(
        UpdatePageRequest $request,
        UpdatePageTask $updatePageTask,
        Page $page
    ): RedirectResponse {
        $updatedPage = $updatePageTask->run($request->all(), $page);

        return back()
            ->withInput()
            ->withErrors($updatedPage->getValidationErrors())
        ;
    }

    /**
     * @param DeletePageMediaRequest $request
     * @param int                    $mediaId
     *
     * @return RedirectResponse
     */
    public function deleteMedia(
        DeletePageMediaRequest $request,
        int $mediaId
    ): RedirectResponse {
        if ($request->validated()) {
            Media::findOrFail($mediaId)->delete();
        }

        return back()
            ->withInput()
            ->withErrors($request->validated())
        ;
    }
}
