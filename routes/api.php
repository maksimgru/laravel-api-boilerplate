<?php

use Dingo\Api\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * Welcome route - link to any public API documentation here
 */

/** @var \Dingo\Api\Routing\Router $api */
$api = app(Router::class);

$api->version('v1', ['middleware' => ['api']], function (Router $api) {
    require base_path('routes/api/auth/auth.php');
    require base_path('routes/api/auth/verify.php');
    require base_path('routes/api/admin/admin.php');
    require base_path('routes/api/front/front.php');
});
