<?php

namespace App\Http\Middleware;

use App\Constants\RouteConstants;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Response;

class OverrideResponseRefreshToken extends Middleware
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @param array                    $guards
     *
     * @return $this|Response|mixed
     * @throws \InvalidArgumentException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        /** @var Response $response */
        $response = $next($request);
        if ($response->status() === Response::HTTP_UNAUTHORIZED
            && $request->route()->getName() === RouteConstants::ROUTE_NAME_REFRESH_TOKEN
        ) {
            $responseContent = json_decode($response->getContent());
            $responseContent->statusCode = Response::HTTP_BAD_REQUEST;
            $response = $response
                ->setContent(json_encode($responseContent))
                ->setStatusCode(Response::HTTP_BAD_REQUEST)
            ;
        }

        return $response;
    }
}
