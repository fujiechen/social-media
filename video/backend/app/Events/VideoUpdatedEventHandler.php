<?php

namespace App\Events;

use App\Services\VideoService;

class VideoUpdatedEventHandler
{
    private VideoService $videoService;

    public function __construct(VideoService $videoService) {
        $this->videoService = $videoService;
    }

    public function handle(VideoUpdatedEvent $event): void {
        $this->videoService->postUpdated($event->video->id);
    }
}
