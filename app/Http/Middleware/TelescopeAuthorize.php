<?php

namespace App\Http\Middleware;

class TelescopeAuthorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return \App::environment(['local', 'staging']) ? $next($request) : abort(403);
    }
}
