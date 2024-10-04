<?php

namespace App\Events;

use App\Services\AlbumService;

class AlbumDeletedEventHandler
{
    private AlbumService $albumService;

    public function __construct(AlbumService $albumService) {
        $this->albumService = $albumService;
    }

    public function handle(AlbumDeletedEvent $event): void
    {
        $this->albumService->postDeleted($event->album);
    }
}
