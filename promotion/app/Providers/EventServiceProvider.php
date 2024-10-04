<?php

namespace App\Providers;

use App\Events\AccountTypeSavedEvent;
use App\Events\AccountTypeSavedEventHandler;
use App\Events\ContactSavedEvent;
use App\Events\ContactSavedEventHandler;
use App\Events\FileSavedEvent;
use App\Events\FileSavedEventHandler;
use App\Events\LandingTemplateSavedEvent;
use App\Events\LandingTemplateSavedEventHandler;
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

        AccountTypeSavedEvent::class => [
            AccountTypeSavedEventHandler::class
        ],

        ContactSavedEvent::class => [
            ContactSavedEventHandler::class
        ],

        LandingTemplateSavedEvent::class => [
            LandingTemplateSavedEventHandler::class
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
