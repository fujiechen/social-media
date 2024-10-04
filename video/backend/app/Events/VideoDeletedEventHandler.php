<?php

namespace App\Events;

use App\Services\VideoService;

class VideoDeletedEventHandler
{
    private VideoService $videoService;

    public function __construct(VideoService $videoService) {
        $this->videoService = $videoService;
    }

    public function handle(VideoDeletedEvent $event): void
    {
        $this->videoService->postDeleted($event->video);
    }
}
