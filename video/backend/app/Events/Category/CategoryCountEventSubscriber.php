<?php

namespace App\Events\Category;

use App\Models\Category;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class CategoryCountEventSubscriber implements ShouldQueue
{
    public function handleAddCategoryViewCountEvent(AddCategoryViewCountEvent $event): void {
        $category = $event->category;
        Category::withoutEvents(function () use ($category) {
            $category->views_count++;
            $category->save();
        });
    }

    public function handleAddMediaCategoryViewCountEvent(AddMediaCategoryViewCountEvent $event): void {
        $media = $event->media;
        foreach ($media->categories as $category) {
            Category::withoutEvents(function () use ($category) {
                $category->views_count++;
                $category->save();
            });
        }
    }

    public function handleSyncCategoryActiveMediaVideoCountEvent(SyncCategoryActiveMediaVideoCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->categories as $category) {
            Category::withoutEvents(function () use ($category, $isActive) {
                if ($isActive) {
                    $category->active_media_videos_count++;
                } else {
                    $category->active_media_videos_count--;
                }
                $category->save();
            });
        }
    }

    public function handleSyncCategoryActiveMediaAlbumCountEvent(SyncCategoryActiveMediaAlbumCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->categories as $category) {
            Category::withoutEvents(function () use ($category, $isActive) {
                if ($isActive) {
                    $category->active_media_albums_count++;
                } else {
                    $category->active_media_albums_count--;
                }
                $category->save();
            });
        }
    }

    public function handleSyncCategoryActiveMediaSeriesCountEvent(SyncCategoryActiveMediaSeriesCountEvent $event): void {
        $media = $event->media;
        $isActive = $event->isActive;

        foreach ($media->categories as $category) {
            Category::withoutEvents(function () use ($category, $isActive) {
                if ($isActive) {
                    $category->active_media_series_count++;
                } else {
                    $category->active_media_series_count--;
                }
                $category->save();
            });
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            AddCategoryViewCountEvent::class => 'handleAddCategoryViewCountEvent',
            AddMediaCategoryViewCountEvent::class => 'handleAddMediaCategoryViewCountEvent',
            SyncCategoryActiveMediaVideoCountEvent::class => 'handleSyncCategoryActiveMediaVideoCountEvent',
            SyncCategoryActiveMediaAlbumCountEvent::class => 'handleSyncCategoryActiveMediaAlbumCountEvent',
            SyncCategoryActiveMediaSeriesCountEvent::class => 'handleSyncCategoryActiveMediaSeriesCountEvent',
        ];
    }
}
