<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        $this->apiPrefix = env('API_PREFIX');

        return $app;
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        $this->artisan('medialibrary:clean', ['--env' => 'testing']);
        $this->artisan('medialibrary:clear', ['--env' => 'testing']);
        $this->artisan('migrate:fresh', ['--seed' => true, '--env' => 'testing']);

        parent::tearDown();
    }
}
