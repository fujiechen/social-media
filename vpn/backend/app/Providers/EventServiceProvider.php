<?php

namespace App\Providers;

use App\Events\ActivityLogSavedEvent;
use App\Events\ActivityLogSavedEventHandler;
use App\Events\CategoryUserCreatedEvent;
use App\Events\CategoryUserCreatedEventHandler;
use App\Events\CategoryUserUpdatedEvent;
use App\Events\CategoryUserUpdatedEventHandler;
use App\Events\FileSavedEvent;
use App\Events\FileSavedEventHandler;
use App\Events\OrderSavedEvent;
use App\Events\OrderSavedEventHandler;
use App\Events\PaymentCreatedEvent;
use App\Events\PaymentCreatedEventHandler;
use App\Events\UserPayoutSavedEvent;
use App\Events\UserPayoutSavedEventHandler;
use App\Events\UserRoleSavedEvent;
use App\Events\UserRoleSavedEventHandler;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        FileSavedEvent::class => [
            FileSavedEventHandler::class
        ],

        ActivityLogSavedEvent::class => [
            ActivityLogSavedEventHandler::class,
        ],

        OrderSavedEvent::class => [
            OrderSavedEventHandler::class,
        ],

        PaymentCreatedEvent::class => [
            PaymentCreatedEventHandler::class,
        ],

        UserPayoutSavedEvent::class => [
            UserPayoutSavedEventHandler::class,
        ],

        CategoryUserUpdatedEvent::class => [
            CategoryUserUpdatedEventHandler::class,
        ],

        CategoryUserCreatedEvent::class => [
            CategoryUserCreatedEventHandler::class,
        ],

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
