<?php

namespace App\Events\Media;

use App\Models\Media;
use App\Services\MediaCommentService;
use App\Services\MediaFavoriteService;
use App\Services\MediaLikeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class MediaCountEventSubscriber implements ShouldQueue
{
    private MediaLikeService $mediaLikeService;
    private MediaFavoriteService $mediaFavoriteService;
    private MediaCommentService $mediaCommentService;

    public function __construct(MediaLikeService $mediaLikeService, MediaFavoriteService $mediaFavoriteService, MediaCommentService $mediaCommentService) {
        $this->mediaLikeService = $mediaLikeService;
        $this->mediaFavoriteService = $mediaFavoriteService;
        $this->mediaCommentService = $mediaCommentService;
    }

    public function handleAddMediaViewCountEvent(AddMediaViewCountEvent $event): void {
        $media = $event->media;
        Media::withoutEvents(function () use ($media) {
            $media->views_count++;
            $media->save();
        });
    }

    public function handleSyncMediaLikeCountEvent(SyncMediaLikeCountEvent $event): void {
        $media = $event->media;
        $total = $this->mediaLikeService->getTotalMediaCount($media->id);
        Media::withoutEvents(function () use ($media, $total) {
            $media->likes_count = $total;
            $media->save();
        });
    }

    public function handleSyncMediaCommentCountEvent(SyncMediaCommentCountEvent $event): void {
        $media = $event->media;
        $total = $this->mediaCommentService->getTotalMediaCount($media->id);
        Media::withoutEvents(function () use ($media, $total) {
            $media->comments_count = $total;
            $media->save();
        });
    }

    public function handleSyncMediaFavoriteCountEvent(SyncMediaFavoriteCountEvent $event): void {
        $media = $event->media;
        $total = $this->mediaFavoriteService->getTotalMediaCount($media->id);
        Media::withoutEvents(function () use ($media, $total) {
            $media->favorites_count = $total;
            $media->save();
        });
    }

    public function handleSyncMediaChildrenCountEvent(SyncMediaChildrenCountEvent $event): void {
        $media = $event->media;
        if (!$media->isSeries()) {
            return;
        }

        $total = $media->childrenMedias->count();
        Media::withoutEvents(function () use ($media, $total) {
            $media->children_count = $total;
            $media->save();
        });
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            AddMediaViewCountEvent::class => 'handleAddMediaViewCountEvent',
            SyncMediaLikeCountEvent::class => 'handleSyncMediaLikeCountEvent',
            SyncMediaCommentCountEvent::class => 'handleSyncMediaCommentCountEvent',
            SyncMediaFavoriteCountEvent::class => 'handleSyncMediaFavoriteCountEvent',
            SyncMediaChildrenCountEvent::class => 'handleSyncMediaChildrenCountEvent',
        ];
    }
}
