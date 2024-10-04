<?php

namespace App\Providers;

use App\Events\ActivityLogSavedEvent;
use App\Events\ActivityLogSavedEventHandler;
use App\Events\Actor\ActorCountEventSubscriber;
use App\Events\AlbumDeletedEvent;
use App\Events\AlbumDeletedEventHandler;
use App\Events\AlbumQueueUpdatedEvent;
use App\Events\AlbumQueueUpdatedEventHandler;
use App\Events\AlbumUpdatedEvent;
use App\Events\AlbumUpdatedEventHandler;
use App\Events\Category\CategoryCountEventSubscriber;
use App\Events\FileSavedEvent;
use App\Events\FileSavedEventHandler;
use App\Events\Media\MediaCountEventSubscriber;
use App\Events\MediaDeletedEvent;
use App\Events\MediaDeletedEventHandler;
use App\Events\MediaSavedEvent;
use App\Events\MediaSavedEventHandler;
use App\Events\OrderSavedEvent;
use App\Events\OrderSavedEventHandler;
use App\Events\PaymentCreatedEvent;
use App\Events\PaymentCreatedEventHandler;
use App\Events\ProductDeletedEvent;
use App\Events\ProductDeletedEventHandler;
use App\Events\ResourceTagActorCategoryUpdateEventSubscriber;
use App\Events\SeriesDeletedEvent;
use App\Events\SeriesDeletedEventHandler;
use App\Events\SeriesUpdatedEvent;
use App\Events\SeriesUpdatedEventHandler;
use App\Events\Tag\TagCountEventSubscriber;
use App\Events\UserPayoutSavedEvent;
use App\Events\UserPayoutSavedEventHandler;
use App\Events\UserRoleSavedEvent;
use App\Events\UserRoleSavedEventHandler;
use App\Events\VideoDeletedEvent;
use App\Events\VideoDeletedEventHandler;
use App\Events\VideoQueueUpdatedEvent;
use App\Events\VideoQueueUpdatedEventHandler;
use App\Events\VideoTagActorCategorySavedEventSubscriber;
use App\Events\VideoUpdatedEvent;
use App\Events\VideoUpdatedEventHandler;
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

        VideoQueueUpdatedEvent::class => [
            VideoQueueUpdatedEventHandler::class
        ],

        AlbumQueueUpdatedEvent::class => [
            AlbumQueueUpdatedEventHandler::class
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

        MediaSavedEvent::class => [
            MediaSavedEventHandler::class,
        ],

        UserPayoutSavedEvent::class => [
            UserPayoutSavedEventHandler::class,
        ],

        UserRoleSavedEvent::class => [
            UserRoleSavedEventHandler::class,
        ],

        VideoUpdatedEvent::class => [
            VideoUpdatedEventHandler::class,
        ],

        AlbumUpdatedEvent::class => [
            AlbumUpdatedEventHandler::class,
        ],

        SeriesUpdatedEvent::class => [
            SeriesUpdatedEventHandler::class,
        ],

        VideoDeletedEvent::class => [
            VideoDeletedEventHandler::class,
        ],

        AlbumDeletedEvent::class => [
            AlbumDeletedEventHandler::class,
        ],

        SeriesDeletedEvent::class => [
            SeriesDeletedEventHandler::class,
        ],

        MediaDeletedEvent::class => [
            MediaDeletedEventHandler::class,
        ],

        ProductDeletedEvent::class => [
            ProductDeletedEventHandler::class,
        ],
    ];

    protected $subscribe = [
        VideoTagActorCategorySavedEventSubscriber::class,
        ResourceTagActorCategoryUpdateEventSubscriber::class,
        MediaCountEventSubscriber::class,
        TagCountEventSubscriber::class,
        CategoryCountEventSubscriber::class,
        ActorCountEventSubscriber::class,
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
