<?php

use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

$api->group(['prefix' => 'devices'], function (Router $api) {
    $api->post('/register', 'App\Http\Controllers\Front\UserDeviceController@register')->middleware(['api.auth']);
    $api->post('/remove', 'App\Http\Controllers\Front\UserDeviceController@remove');
});

