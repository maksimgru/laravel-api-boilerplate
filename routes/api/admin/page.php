<?php

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

$api->group(['prefix' => 'pages', 'middleware' => 'check_role:' . RoleConstants::ROLE_ADMIN], function (Router $api) {
    $api->get('/', 'App\Http\Controllers\Admin\PageController@index')->name(RouteConstants::ROUTE_NAME_PAGES);
    $api->get('/trash', 'App\Http\Controllers\Admin\PageController@getSoftDeletedPages')->name(RouteConstants::ROUTE_NAME_DELETED_PAGES);
    $api->get('/{page}', 'App\Http\Controllers\Admin\PageController@getSinglePage');
    $api->get('/trash/{page}', 'App\Http\Controllers\Admin\PageController@getSingleSoftDeletedPage');
    $api->post('/', 'App\Http\Controllers\Admin\PageController@postPage');
    $api->post('/{page}', 'App\Http\Controllers\Admin\PageController@updatePage');

    $api->delete('/{page}', 'App\Http\Controllers\Admin\PageController@deletePage')->name(RouteConstants::ROUTE_NAME_DELETE_PAGE);
    $api->delete('/force/{page}', 'App\Http\Controllers\Admin\PageController@forceDeletePage')->name(RouteConstants::ROUTE_NAME_FORCE_DELETE_PAGE);
    $api->get('/restore/{page}', 'App\Http\Controllers\Admin\PageController@restorePage')->name(RouteConstants::ROUTE_NAME_RESTORE_PAGE);
});
