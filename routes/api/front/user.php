<?php

use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

/*
 * Anonymous routes
 */
$api->group(['prefix' => 'users'], function (Router $api) {
    /** Users */
    $api->post('/register', 'App\Http\Controllers\Front\UserController@register');
});

/*
 * Authenticated routes
 */
$api->group(['middleware' => ['api.auth']], function (Router $api) {
    /** Users */
    $api->group(['prefix' => 'users'], function (Router $api) {
        $api->get('/me', 'App\Http\Controllers\Front\UserController@getCurrentUser');
        $api->post('/me', 'App\Http\Controllers\Front\UserController@updateCurrentUser');
        $api->delete('/me/media/{media}', 'App\Http\Controllers\Front\UserController@deleteMedia');

        $api->get('/me/get_favorite_visit_places', 'App\Http\Controllers\Front\UserController@getFavoriteVisitPlaces');
        $api->post('/me/add_favorite_visit_places', 'App\Http\Controllers\Front\UserController@addFavoriteVisitPlaces');
        $api->delete('/me/remove_favorite_visit_place/{visit_place_id}', 'App\Http\Controllers\Front\UserController@removeFavoriteVisitPlace');
    });
});
