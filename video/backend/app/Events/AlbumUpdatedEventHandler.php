<?php

namespace App\Events;

use App\Services\AlbumService;

class AlbumUpdatedEventHandler
{
    private AlbumService $albumService;

    public function __construct(AlbumService $albumService) {
        $this->albumService = $albumService;
    }

    public function handle(AlbumUpdatedEvent $event): void {
        $this->albumService->postUpdated($event->album->id);
    }
}
