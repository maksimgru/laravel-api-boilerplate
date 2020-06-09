<?php

use App\Constants\RouteConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

/*
 * Anonymous routes
 */
$api->group(['prefix' => 'auth'], function (Router $api) {
    $api->group(['prefix' => 'jwt'], function (Router $api) {
        $api->get('/token', 'App\Http\Controllers\Auth\AuthController@getToken')->name(RouteConstants::ROUTE_NAME_AUTH_BASIC_TOKEN);
        $api->post('/login', 'App\Http\Controllers\Auth\AuthController@login')->name(RouteConstants::ROUTE_NAME_LOGIN);
        $api->post('/password/reset', 'App\Http\Controllers\Auth\AuthController@resetPassword')->name(RouteConstants::ROUTE_NAME_PASSWORD_RESET);
        $api->post('/password/restore', 'App\Http\Controllers\Auth\AuthController@restorePassword')->name(RouteConstants::ROUTE_NAME_PASSWORD_RESTORE);
        $api->get('/social/{provider}/login', 'App\Http\Controllers\Auth\AuthController@socialLogin')->name(RouteConstants::ROUTE_NAME_SOCIAL_LOGIN);
        $api->get('/social/{provider}/callback', 'App\Http\Controllers\Auth\AuthController@handleProviderCallback')->name(RouteConstants::ROUTE_NAME_SOCIAL_PROVIDER_CALLBACK);
        $api->post('/social/{provider}/token', 'App\Http\Controllers\Auth\AuthController@handleProviderByToken')->name(RouteConstants::ROUTE_NAME_SOCIAL_PROVIDER_BY_TOKEN);
    });
});

/*
 * Authenticated routes
 */
$api->group(['middleware' => ['override_response', 'api.auth']], function (Router $api) {
    /** Authentication */
    $api->group(['prefix' => 'auth'], function (Router $api) {
        $api->group(['prefix' => 'jwt'], function (Router $api) {
            $api->get('/refresh', 'App\Http\Controllers\Auth\AuthController@refreshToken')->name(RouteConstants::ROUTE_NAME_REFRESH_TOKEN);
            $api->delete('/logout', 'App\Http\Controllers\Auth\AuthController@invalidateToken')->name(RouteConstants::ROUTE_NAME_LOGOUT);
        });
    });
});
