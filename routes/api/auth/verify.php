<?php

use App\Constants\RouteConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

// Email Verification routes
$api->group(['prefix' => 'email', 'middleware' => ['api.auth']], function (Router $api) {
    $api->group(['prefix' => 'verify'], function (Router $api) {
        $api->get('/notice', 'App\Http\Controllers\Auth\VerificationController@show')->name(RouteConstants::ROUTE_NAME_EMAIL_VERIFY_NOTICE);
        $api->get('/user/{user_id}/{verification_token}', 'App\Http\Controllers\Auth\VerificationController@verify')->name(RouteConstants::ROUTE_NAME_EMAIL_VERIFY);
        $api->get('/resend', 'App\Http\Controllers\Auth\VerificationController@resend')->name(RouteConstants::ROUTE_NAME_EMAIL_VERIFY_RESEND);
    });
});
