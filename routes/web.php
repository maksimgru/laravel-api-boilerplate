<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Constants\RoleConstants;
use App\Constants\RouteConstants;

Auth::routes();

Route::middleware(['auth:web'])->group(function () {
    // Email Verification Routes...
    Route::get('email/verify/notice', 'Auth\VerificationController@show')->name(RouteConstants::ROUTE_NAME_WEB_EMAIL_VERIFY_NOTICE);
    Route::get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->name(RouteConstants::ROUTE_NAME_WEB_EMAIL_VERIFY);
    Route::post('email/resend', 'Auth\VerificationController@resend')->name(RouteConstants::ROUTE_NAME_WEB_EMAIL_VERIFY_RESEND);
});

Route::group(['middleware' => ['auth:web', 'check_role:' . implode(',', RoleConstants::ALL_ADMIN_AREA_ROLES)]], function () {
    Route::get('/', 'HomeController@index')->name(RouteConstants::ROUTE_NAME_WEB_HOME);

    Route::get('/users', 'UsersController@index')->name(RouteConstants::ROUTE_NAME_WEB_USERS);
    Route::get('/users/me', 'UsersController@showCurrentUser')->name(RouteConstants::ROUTE_NAME_WEB_MY_PROFILE);
    Route::post('/users/me', 'UsersController@updateCurrentUser')->name(RouteConstants::ROUTE_NAME_WEB_UPDATE_MY_PROFILE);
    Route::get('/users/{user_id}', 'UsersController@showUser')->name(RouteConstants::ROUTE_NAME_WEB_USER_PROFILE);
    Route::post('/users/{user_id}', 'UsersController@updateUser')->name(RouteConstants::ROUTE_NAME_WEB_UPDATE_USER_PROFILE);
    Route::get('/users-new', 'UsersController@showNewUser')->name(RouteConstants::ROUTE_NAME_WEB_NEW_USER_PROFILE);
    Route::post('/users-new', 'UsersController@createNewUser')->name(RouteConstants::ROUTE_NAME_WEB_CREATE_NEW_USER_PROFILE);
    Route::post('/users/media/delete/{media}', 'UsersController@deleteUserMedia')->name(RouteConstants::ROUTE_NAME_WEB_DELETE_USER_MEDIA);
});

Route::group(['middleware' => ['auth:web', 'check_role:'. RoleConstants::ROLE_ADMIN]], function () {
    Route::get('/pages', 'PagesController@index')->name(RouteConstants::ROUTE_NAME_WEB_PAGES);
    Route::get('/pages/{page}', 'PagesController@show')->name(RouteConstants::ROUTE_NAME_WEB_PAGE_VIEW);
    Route::post('/pages/{page}', 'PagesController@update')->name(RouteConstants::ROUTE_NAME_WEB_PAGE_UPDATE);
    Route::get('/pages-new', 'PagesController@showNew')->name(RouteConstants::ROUTE_NAME_WEB_PAGE_VIEW_NEW);
    Route::post('/pages-new', 'PagesController@createNew')->name(RouteConstants::ROUTE_NAME_WEB_PAGE_CREATE_NEW);
    Route::post('/pages/media/delete/{media}', 'PagesController@deleteMedia')->name(RouteConstants::ROUTE_NAME_WEB_DELETE_PAGE_MEDIA);

    Route::get('/roles', 'RolesController@index')->name(RouteConstants::ROUTE_NAME_WEB_ROLES);
    Route::get('/settings', 'SettingsController@index')->name(RouteConstants::ROUTE_NAME_WEB_SETTINGS);
    Route::get('/trash', 'TrashController@index')->name(RouteConstants::ROUTE_NAME_WEB_TRASH);
});

