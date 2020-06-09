<?php

use App\Constants\RoleConstants;
use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */
$api = app(Router::class);

/*
 * Authenticated routes
 */
$api->group(['middleware' => ['api.auth']], function (Router $api) {
    $api->group(['prefix' => 'admin', 'middleware' => 'check_role:' . implode(',', RoleConstants::ALL_ADMIN_ROLES)], function (Router $api) {
        require base_path('routes/api/admin/role.php');
        require base_path('routes/api/admin/user.php');
        require base_path('routes/api/admin/page.php');
        require base_path('routes/api/admin/media.php');
        require base_path('routes/api/admin/visit_place_category.php');
        require base_path('routes/api/admin/visit_place.php');
        require base_path('routes/api/admin/visit_place_comment.php');
        require base_path('routes/api/admin/visit_place_rating.php');
        require base_path('routes/api/admin/transaction.php');
    });
});
