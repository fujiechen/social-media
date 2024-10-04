<?php

namespace App\Events;

use App\Services\MediaService;

class MediaDeletedEventHandler
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService) {
        $this->mediaService = $mediaService;
    }

    public function handle(MediaDeletedEvent $event): void
    {
        $this->mediaService->postDeleted($event->media);
    }
}
