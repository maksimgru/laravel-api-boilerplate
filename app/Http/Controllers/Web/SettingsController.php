<?php

namespace App\Http\Controllers\Web;

use App\Constants\RouteConstants;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Redirector;

class SettingsController extends Controller
{
    /**
     * @return Redirector | Renderable
     * @throws Exception
     */
    public function index() {
        if (!auth()->user()->isAdmin()) {
            return redirect(route(RouteConstants::ROUTE_NAME_WEB_HOME));
        }

        return view('admin-pages.settings', []);
    }
}
