<?php

namespace App\Providers;

use App\Events\ActivityLogSavedEvent;
use App\Events\ActivityLogSavedEventHandler;
use App\Events\TestEvent;
use App\Events\TestEventHandler;
use App\Events\UserOrderNotificationCreatedEvent;
use App\Events\UserOrderNotificationCreatedEventHandler;
use App\Events\UserOrderPaymentCreatedEvent;
use App\Events\UserOrderPaymentCreatedEventHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ActivityLogSavedEvent::class => [
            ActivityLogSavedEventHandler::class,
        ],

        UserOrderPaymentCreatedEvent::class => [
            UserOrderPaymentCreatedEventHandler::class,
        ],

        UserOrderNotificationCreatedEvent::class => [
            UserOrderNotificationCreatedEventHandler::class,
        ],

        TestEvent::class => [
            TestEventHandler::class,
        ],
    ];

    protected $subscribe = [
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
