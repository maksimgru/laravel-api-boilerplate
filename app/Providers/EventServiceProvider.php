<?php

namespace App\Providers;

use App\Events\UserDeviceDeletedEvent;
use App\Listeners\RemoveDeviceOnAmazonSide;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserDeviceDeletedEvent::class => [
            RemoveDeviceOnAmazonSide::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
