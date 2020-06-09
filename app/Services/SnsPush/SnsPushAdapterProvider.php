<?php

namespace App\Services\SnsPush;

use Illuminate\Support\ServiceProvider;
use SNSPush\Exceptions\SNSPushException;

class SnsPushAdapterProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     * @throws SNSPushException
     * @throws \InvalidArgumentException
     */
    public function register()
    {
        $this->app->singleton(SnsPushAdapter::class, function () {
            return new SnsPushAdapter(config('services.sns'));
        });
    }

    /**
     * Tell what services this package provides.
     *
     * @return array
     */
    public function provides(): array
    {
        return [SnsPushAdapter::class];
    }
}
