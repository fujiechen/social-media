<?php

namespace App\Events;

use App\Services\MediaQueueService;
use App\Services\SeriesQueueService;

class AlbumQueueUpdatedEventHandler
{
    private MediaQueueService $mediaQueueService;
    private SeriesQueueService $seriesQueueService;

    public function __construct(MediaQueueService $mediaQueueService, SeriesQueueService $seriesQueueService) {
        $this->mediaQueueService = $mediaQueueService;
        $this->seriesQueueService = $seriesQueueService;
    }

    public function handle(AlbumQueueUpdatedEvent $albumQueueUpdatedEvent): bool {
        $this->seriesQueueService->updateSeriesQueueStatusOfAlbumQueue($albumQueueUpdatedEvent->albumQueue->id);
        $this->mediaQueueService->updateMediaQueueStatusOfAlbumQueue($albumQueueUpdatedEvent->albumQueue->id);
        return true;
    }

}
