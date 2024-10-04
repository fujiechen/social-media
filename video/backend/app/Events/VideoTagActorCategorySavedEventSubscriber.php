<?php

namespace App\Events;

use App\Services\MediaActorService;
use App\Services\MediaCategoryService;
use App\Services\MediaService;
use App\Services\MediaTagService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class VideoTagActorCategorySavedEventSubscriber implements ShouldQueue
{
    private MediaService $mediaService;
    private MediaTagService $mediaTagService;
    private MediaCategoryService $mediaCategoryService;
    private MediaActorService $mediaActorService;

    public function __construct(MediaService $mediaService, MediaTagService $mediaTagService,
                                MediaActorService $mediaActorService, MediaCategoryService $mediaCategoryService) {
        $this->mediaService = $mediaService;
        $this->mediaTagService = $mediaTagService;
        $this->mediaCategoryService = $mediaCategoryService;
        $this->mediaActorService = $mediaActorService;
    }

    public function handleVideoTagSavedEvent(VideoTagSavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByVideo($event->videoTag->video_id);
        foreach ($medias as $media) {
            $this->mediaTagService->updateOrCreateMediaTag($media->id);
        }
    }

    public function handleVideoActorSavedEvent(VideoActorSavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByVideo($event->videoActor->video_id);
        foreach ($medias as $media) {
            $this->mediaActorService->updateOrCreateMediaActor($media->id);
        }
    }

    public function handleVideoCategorySavedEvent(VideoCategorySavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByVideo($event->videoCategory->video_id);
        foreach ($medias as $media) {
            $this->mediaCategoryService->updateOrCreateMediaCategory($media->id);
        }
    }

    public function handleAlbumTagSavedEvent(AlbumTagSavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByAlbum($event->albumTag->album_id);
        foreach ($medias as $media) {
            $this->mediaTagService->updateOrCreateMediaTag($media->id);
        }
    }

    public function handleAlbumActorSavedEvent(AlbumActorSavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByAlbum($event->albumActor->album_id);
        foreach ($medias as $media) {
            $this->mediaActorService->updateOrCreateMediaActor($media->id);
        }
    }

    public function handleAlbumCategorySavedEvent(AlbumCategorySavedEvent $event): void {
        $medias = $this->mediaService->fetchAllMediasByAlbum($event->albumCategory->album_id);
        foreach ($medias as $media) {
            $this->mediaCategoryService->updateOrCreateMediaCategory($media->id);
        }
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            VideoTagSavedEvent::class => 'handleVideoTagSavedEvent',
            VideoActorSavedEvent::class => 'handleVideoActorSavedEvent',
            VideoCategorySavedEvent::class => 'handleVideoCategorySavedEvent',
            AlbumTagSavedEvent::class => 'handleAlbumTagSavedEvent',
            AlbumActorSavedEvent::class => 'handleAlbumActorSavedEvent',
            AlbumCategorySavedEvent::class => 'handleAlbumCategorySavedEvent',
        ];
    }
}
