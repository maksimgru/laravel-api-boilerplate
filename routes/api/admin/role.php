<?php

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

$api->group(['prefix' => 'roles', 'middleware' => 'check_role:' . RoleConstants::ROLE_ADMIN], function (Router $api) {
    $api->get('/', 'App\Http\Controllers\Admin\RoleController@index')->name(RouteConstants::ROUTE_NAME_ROLES);
});
