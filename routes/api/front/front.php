<?php

use Dingo\Api\Routing\Router;

/** @var \Dingo\Api\Routing\Router $api */
$api = app(Router::class);

require base_path('routes/api/front/user.php');
require base_path('routes/api/front/user_device.php');
require base_path('routes/api/front/page.php');
