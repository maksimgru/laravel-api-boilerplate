<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';

/** @var \Illuminate\Foundation\Console\Kernel $console */
$console = $app->make(Illuminate\Contracts\Console\Kernel::class);
$console->bootstrap();

// Clear all app cache
$app->cache->clear();
