<?php

namespace App\Http\Middleware;

use App\Exceptions\Auth\UserNotActivatedException;
use Closure;

class CheckUserActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws UserNotActivatedException
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user() && !auth()->user()->isActive()) {
            auth()->logout();
            throw new UserNotActivatedException(trans('auth.user.not_activated'));
        }

        return $next($request);
    }
}
