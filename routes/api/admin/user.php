<?php

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */

// For Admin, Manager, Business, Worker
$api->group(['prefix' => 'users'], function (Router $api) {
    $api->group(['prefix' => 'tourists'], function (Router $api) {
        $api->get('/', 'App\Http\Controllers\Admin\UserController@getActiveTouristUsers')->name(RouteConstants::ROUTE_NAME_TOURIST_USERS);
        $api->get('/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleActiveTouristUser')->name(RouteConstants::ROUTE_NAME_TOURIST_USER);
    });
});

// @TODO deprecated need remove
$api->get('/manager/tourists/referrals/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleActiveTouristUser');
$api->get('/business/tourists/referrals/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleActiveTouristUser');

// For Admins
$api->group(['prefix' => 'users', 'middleware' => 'check_role:' . RoleConstants::ROLE_ADMIN], function (Router $api) {
    $api->get('/', 'App\Http\Controllers\Admin\UserController@index')->name(RouteConstants::ROUTE_NAME_USERS);
    $api->get('/trash', 'App\Http\Controllers\Admin\UserController@getSoftDeletedUsers')->name(RouteConstants::ROUTE_NAME_DELETED_USERS);
    $api->get('/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleUser')->name(RouteConstants::ROUTE_NAME_USER);
    $api->get('/trash/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleSoftDeletedUser');
    $api->post('/', 'App\Http\Controllers\Admin\UserController@postUser');
    $api->post('/{user_id}', 'App\Http\Controllers\Admin\UserController@updateUser');
    $api->delete('/{user_id}', 'App\Http\Controllers\Admin\UserController@deleteUser')->name(RouteConstants::ROUTE_NAME_DELETE_USER);
    $api->delete('/force/{user_id}', 'App\Http\Controllers\Admin\UserController@forceDeleteUser')->name(RouteConstants::ROUTE_NAME_FORCE_DELETE_USER);
    $api->get('/restore/{user_id}', 'App\Http\Controllers\Admin\UserController@restoreUser')->name(RouteConstants::ROUTE_NAME_RESTORE_USER);
});

// For Managers
$api->group(['prefix' => 'manager', 'middleware' => 'check_role:' . RoleConstants::ROLE_MANAGER], function (Router $api) {
    $api->group(['prefix' => 'businesses'], function (Router $api) {
        $api->get('/', 'App\Http\Controllers\Admin\UserController@getOwnBusinessUsers')->name(RouteConstants::ROUTE_NAME_MANAGER_OWN_BUSINESS_USERS);
        $api->get('/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleOwnBusinessUser')->name(RouteConstants::ROUTE_NAME_MANAGER_OWN_BUSINESS_USER);
        $api->post('/', 'App\Http\Controllers\Admin\UserController@postOwnBusinessUser');
    });

    $api->group(['prefix' => 'workers'], function (Router $api) {
        $api->get('/', 'App\Http\Controllers\Admin\UserController@getOwnBusinessWorkerUsers')->name(RouteConstants::ROUTE_NAME_MANAGER_OWN_BUSINESS_WORKER_USERS);
        $api->get('/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleOwnBusinessWorkerUser');
    });

    $api->group(['prefix' => 'tourists'], function (Router $api) {
        $api->get('/visited', 'App\Http\Controllers\Admin\UserController@getManagerVisitedTouristUsers')->name(RouteConstants::ROUTE_NAME_MANAGER_VISITED_TOURIST_USERS);
        $api->get('/referrals', 'App\Http\Controllers\Admin\UserController@getManagerReferralTouristUsers')->name(RouteConstants::ROUTE_NAME_MANAGER_REFERRAL_TOURIST_USERS);
    });
});

// For Businesses
$api->group(['prefix' => 'business', 'middleware' => 'check_role:' . RoleConstants::ROLE_BUSINESS], function (Router $api) {
    $api->group(['prefix' => 'workers'], function (Router $api) {
        $api->get('/', 'App\Http\Controllers\Admin\UserController@getOwnWorkerUsers')->name(RouteConstants::ROUTE_NAME_BUSINESS_OWN_WORKER_USERS);
        $api->get('/{user_id}', 'App\Http\Controllers\Admin\UserController@getSingleOwnWorkerUser');
        $api->post('/', 'App\Http\Controllers\Admin\UserController@postOwnWorkerUser');
    });

    $api->group(['prefix' => 'tourists'], function (Router $api) {
        $api->get('/visited', 'App\Http\Controllers\Admin\UserController@getBusinessVisitedTouristUsers')->name(RouteConstants::ROUTE_NAME_BUSINESS_VISITED_TOURIST_USERS);
        $api->get('/referrals', 'App\Http\Controllers\Admin\UserController@getBusinessReferralTouristUsers')->name(RouteConstants::ROUTE_NAME_BUSINESS_REFERRAL_TOURIST_USERS);
    });
});
