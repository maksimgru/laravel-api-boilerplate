<?php

namespace App\Http\Controllers\Web;

use App\Constants\RouteConstants;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Support\Renderable;

class RolesController extends Controller
{
    /**
     * @return Renderable
     * @throws Exception
     */
    public function index(): Renderable {
        return view('admin-pages.roles', [
            'list_page_title'     => trans('Roles'),
            'table_load_data_url' => apiRoute(RouteConstants::ROUTE_NAME_ROLES),
        ]);
    }
}
