<?php

namespace App\Http\Middleware;

use Closure;

class SetDefaultWebAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        app('auth')->setDefaultDriver('web');

        return $next($request);
    }
}
