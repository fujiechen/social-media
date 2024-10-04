<?php

namespace App\Events;


use App\Models\Media;
use App\Services\Cache\CacheService;
use App\Services\Cache\MediaCacheService;
use App\Services\MediaActorService;
use App\Services\MediaCategoryService;
use App\Services\MediaSearchService;
use App\Services\MediaService;
use App\Services\MediaTagService;

class MediaSavedEventHandler
{
    private MediaService $mediaService;
    private MediaSearchService $mediaSearchService;
    private MediaTagService $mediaTagService;
    private MediaCategoryService $mediaCategoryService;
    private MediaActorService $mediaActorService;
    private CacheService $cacheService;

    public function __construct(
        MediaService         $mediaService,
        MediaSearchService   $mediaSearchService,
        MediaTagService      $mediaTagService,
        MediaActorService    $mediaActorService,
        MediaCategoryService $mediaCategoryService,
        CacheService $cacheService)
    {
        $this->mediaService = $mediaService;
        $this->mediaSearchService = $mediaSearchService;
        $this->mediaTagService = $mediaTagService;
        $this->mediaCategoryService = $mediaCategoryService;
        $this->mediaActorService = $mediaActorService;
        $this->cacheService = $cacheService;
    }

    public function handle(MediaSavedEvent $mediaSavedEvent): void
    {
        $media = $mediaSavedEvent->media;

        $isUpdate = !is_null($media->getOriginal('id'));
        $status = $media->status;

        // for update and active changed only
        if ($isUpdate && $media->getOriginal('status') != $status
            && ($media->getOriginal('status')  == Media::STATUS_ACTIVE || $status == Media::STATUS_ACTIVE)) {
            $this->mediaService->postToggleActive($media->id, $status);
        }

        $this->mediaTagService->updateOrCreateMediaTag($mediaSavedEvent->media->id);
        $this->mediaActorService->updateOrCreateMediaActor($mediaSavedEvent->media->id);
        $this->mediaCategoryService->updateOrCreateMediaCategory($mediaSavedEvent->media->id);
        $this->mediaSearchService->reBuildMediaSearchText($mediaSavedEvent->media->id);

        $this->cacheService->deleteCacheByPrefix(MediaCacheService::PREFIX);
    }
}
