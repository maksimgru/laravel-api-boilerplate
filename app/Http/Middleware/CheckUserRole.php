<?php

namespace App\Http\Middleware;

use Closure;
use Specialtactics\L5Api\Exceptions\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param array|string              $roles Roles to check for
     *
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @throws \Specialtactics\L5Api\Exceptions\UnauthorizedHttpException
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $loggedInUser = $request->user();

        // Check user is logged in
        if (empty($loggedInUser)) {
            throw new UnauthorizedHttpException(trans('Unauthorized - not logged in !!!'));
        }

        if (! is_array($roles)) {
            $roles = [$roles];
        }

        // Match user roles to requested roles
        $matchedRoles = array_intersect($roles, $loggedInUser->getRoles());
        if (empty($matchedRoles)) {
            if (!isApiRequest($request)) {
                auth()->logout();
            }
            throw new AccessDeniedHttpException(trans('You do not have the permission to use this resource.'));
        }

        return $next($request);
    }
}
