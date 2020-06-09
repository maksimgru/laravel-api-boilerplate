<?php

use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

/*
 * Anonymous routes
 */
$api->group(['prefix' => 'pages'], function (Router $api) {
    $api->get('/', 'App\Http\Controllers\Front\PageController@index');
    $api->get('/{page}', 'App\Http\Controllers\Front\PageController@getSinglePage');
});
