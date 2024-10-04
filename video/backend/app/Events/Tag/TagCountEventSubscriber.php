<?php

namespace App\Events\Tag;

use App\Models\Tag;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class TagCountEventSubscriber implements ShouldQueue
{
    public function handleAddTagViewCountEvent(AddTagViewCountEvent $event): void {
        $tag = $event->tag;
        Tag::withoutEvents(function () use ($tag) {
            $tag->views_count++;
            $tag->save();
        });
    }

    public function handleAddMediaTagViewCountEvent(AddMediaTagViewCountEvent $event): void {
        $media = $event->media;
        foreach ($media->tags as $tag) {
            Tag::withoutEvents(function () use ($tag) {
                $tag->views_count++;
                $tag->save();
            });
        }
    }

    public function handleSyncTagActiveMediaVideoCountEvent(SyncTagActiveMediaVideoCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->tags as $tag) {
            Tag::withoutEvents(function () use ($tag, $isActive) {
                if ($isActive) {
                    $tag->active_media_videos_count++;
                } else {
                    $tag->active_media_videos_count--;
                }
                $tag->save();
            });
        }
    }

    public function handleSyncTagActiveMediaAlbumCountEvent(SyncTagActiveMediaAlbumCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->tags as $tag) {
            Tag::withoutEvents(function () use ($tag, $isActive) {
                if ($isActive) {
                    $tag->active_media_albums_count++;
                } else {
                    $tag->active_media_albums_count--;
                }
                $tag->save();
            });
        }
    }

    public function handleSyncTagActiveMediaSeriesCountEvent(SyncTagActiveMediaSeriesCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->tags as $tag) {
            Tag::withoutEvents(function () use ($tag, $isActive) {
                if ($isActive) {
                    $tag->active_media_series_count++;
                } else {
                    $tag->active_media_series_count--;
                }
                $tag->save();
            });
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            AddTagViewCountEvent::class => 'handleAddTagViewCountEvent',
            AddMediaTagViewCountEvent::class => 'handleAddMediaTagViewCountEvent',
            SyncTagActiveMediaVideoCountEvent::class => 'handleSyncTagActiveMediaVideoCountEvent',
            SyncTagActiveMediaAlbumCountEvent::class => 'handleSyncTagActiveMediaAlbumCountEvent',
            SyncTagActiveMediaSeriesCountEvent::class => 'handleSyncTagActiveMediaSeriesCountEvent',
        ];
    }
}
