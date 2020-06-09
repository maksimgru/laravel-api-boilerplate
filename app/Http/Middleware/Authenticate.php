<?php

namespace App\Http\Middleware;

use App\Constants\RouteConstants;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     *
     * @return string
     * @throws UnauthorizedHttpException
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            throw new UnauthorizedHttpException(trans('Not logged in'));
        } else {
            return route(RouteConstants::ROUTE_NAME_WEB_LOGIN);
        }
    }
}
