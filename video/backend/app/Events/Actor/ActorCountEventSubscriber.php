<?php

namespace App\Events\Actor;

use App\Models\Actor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class ActorCountEventSubscriber implements ShouldQueue
{
    public function handleAddActorViewCountEvent(AddActorViewCountEvent $event): void {
        $actor = $event->actor;
        Actor::withoutEvents(function () use ($actor) {
            $actor->views_count++;
            $actor->save();
        });
    }

    public function handleAddMediaActorViewCountEvent(AddMediaActorViewCountEvent $event): void {
        $media = $event->media;
        foreach ($media->actors as $actor) {
            Actor::withoutEvents(function () use ($actor) {
                $actor->views_count++;
                $actor->save();
            });
        }
    }

    public function handleSyncActorActiveMediaVideoCountEvent(SyncActorActiveMediaVideoCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->actors as $actor) {
            Actor::withoutEvents(function () use ($actor, $isActive) {
                if ($isActive) {
                    $actor->active_media_videos_count++;
                } else {
                    $actor->active_media_videos_count--;
                }
                $actor->save();
            });
        }
    }

    public function handleSyncActorActiveMediaAlbumCountEvent(SyncActorActiveMediaAlbumCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->actors as $actor) {
            Actor::withoutEvents(function () use ($actor, $isActive) {
                if ($isActive) {
                    $actor->active_media_albums_count++;
                } else {
                    $actor->active_media_albums_count--;
                }
                $actor->save();
            });
        }
    }

    public function handleSyncActorActiveMediaSeriesCountEvent(SyncActorActiveMediaSeriesCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->actors as $actor) {
            Actor::withoutEvents(function () use ($actor, $isActive) {
                if ($isActive) {
                    $actor->active_media_series_count++;
                } else {
                    $actor->active_media_series_count--;
                }
                $actor->save();
            });
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            AddActorViewCountEvent::class => 'handleAddActorViewCountEvent',
            AddMediaActorViewCountEvent::class => 'handleAddMediaActorViewCountEvent',
            SyncActorActiveMediaVideoCountEvent::class => 'handleSyncActorActiveMediaVideoCountEvent',
            SyncActorActiveMediaAlbumCountEvent::class => 'handleSyncActorActiveMediaAlbumCountEvent',
            SyncActorActiveMediaSeriesCountEvent::class => 'handleSyncActorActiveMediaSeriesCountEvent',
        ];
    }
}
