<?php

namespace App\Http\Middleware;

use App\Exceptions\Auth\EmailNotVerifiedException;
use Closure;

class CheckUserEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws EmailNotVerifiedException
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user() && !auth()->user()->hasVerifiedEmail()) {
            throw new EmailNotVerifiedException(trans('auth.email.not_verified'));
        }

        return $next($request);
    }
}
