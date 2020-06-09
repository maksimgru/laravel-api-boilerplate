<?php

namespace App\Http\Controllers\Web;

use App\Constants\RouteConstants;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;

class TrashController extends Controller
{
    /**
     * @return Renderable
     */
    public function index(): Renderable {
        return view('admin-pages.trash', [
            'user_options' => [
                'table_title'         => trans('Deleted Users'),
                'table_load_data_url' => apiRoute(RouteConstants::ROUTE_NAME_DELETED_USERS),
                'default_order'       => ['id', 'desc'],
                'url_restore_row'     => apiRoute(RouteConstants::ROUTE_NAME_RESTORE_USER, ['user_id' => '%id%']),
                'url_delete_row'      => apiRoute(RouteConstants::ROUTE_NAME_FORCE_DELETE_USER, ['user_id' => '%id%']),
            ],
            'visit_place_options' => [
                'table_title'               => trans('Deleted Visit Places'),
                'table_load_data_url'       => apiRoute(RouteConstants::ROUTE_NAME_DELETED_VISIT_PLACES),
                'default_order'             => ['id', 'desc'],
                'url_restore_row'           => apiRoute(RouteConstants::ROUTE_NAME_RESTORE_VISIT_PLACE, ['visit_place' => '%id%']),
                'url_delete_row'            => apiRoute(RouteConstants::ROUTE_NAME_FORCE_DELETE_VISIT_PLACE, ['visit_place' => '%id%']),
                'url_visit_place_category'  => route(RouteConstants::ROUTE_NAME_WEB_VISIT_PLACE_CATEGORY_VIEW, ['category' => '%id%']),
                'url_business_user_profile' => route(RouteConstants::ROUTE_NAME_WEB_USER_PROFILE, ['user_id' => '%id%']),
            ],
            'visit_place_comment_options' => [
                'table_title'               => trans('Deleted Visit Place Comments'),
                'table_load_data_url'       => apiRoute(RouteConstants::ROUTE_NAME_DELETED_VISIT_PLACE_COMMENTS),
                'default_order'             => ['id', 'desc'],
                'url_restore_row'           => apiRoute(RouteConstants::ROUTE_NAME_RESTORE_VISIT_PLACE_COMMENT, ['comment' => '%id%']),
                'url_delete_row'            => apiRoute(RouteConstants::ROUTE_NAME_FORCE_DELETE_VISIT_PLACE_COMMENT, ['comment' => '%id%']),
                'url_visit_place'           => route(RouteConstants::ROUTE_NAME_WEB_VISIT_PLACE_VIEW, ['visit_place' => '%id%']),
                'url_user_profile'          => route(RouteConstants::ROUTE_NAME_WEB_USER_PROFILE, ['user_id' => '%id%']),
            ],
            'page_options' => [
                'table_title'         => trans('Deleted Pages'),
                'table_load_data_url' => apiRoute(RouteConstants::ROUTE_NAME_DELETED_PAGES),
                'default_order'       => ['id', 'desc'],
                'url_restore_row'     => apiRoute(RouteConstants::ROUTE_NAME_RESTORE_PAGE, ['page' => '%id%']),
                'url_delete_row'      => apiRoute(RouteConstants::ROUTE_NAME_FORCE_DELETE_PAGE, ['page' => '%id%']),
            ],
        ]);
    }
}
