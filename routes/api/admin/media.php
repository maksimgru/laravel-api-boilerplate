<?php

use App\Constants\RoleConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

$api->group(['prefix' => 'media', 'middleware' => 'check_role:' . implode(',', RoleConstants::ALL_ADMIN_AREA_ROLES)], function (Router $api) {
    $api->delete('/{media}', 'App\Http\Controllers\Admin\MediaController@deleteMedia');
});
