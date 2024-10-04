<?php

namespace App\Events;

use App\Models\VideoQueue;
use App\Services\MediaQueueService;
use App\Services\SeriesQueueService;

class VideoQueueUpdatedEventHandler
{
    private MediaQueueService $mediaQueueService;
    private SeriesQueueService $seriesQueueService;

    public function __construct(MediaQueueService $mediaQueueService, SeriesQueueService $seriesQueueService) {
        $this->mediaQueueService = $mediaQueueService;
        $this->seriesQueueService = $seriesQueueService;
    }

    public function handle(VideoQueueUpdatedEvent $videoQueueUpdatedEvent): bool {
        if ($videoQueueUpdatedEvent->videoQueue->status == VideoQueue::STATUS_PENDING) {
            return true;
        }

        $this->seriesQueueService->updateSeriesQueueStatusOfVideoQueue($videoQueueUpdatedEvent->videoQueue->id);
        $this->mediaQueueService->updateMediaQueueStatusOfVideoQueue($videoQueueUpdatedEvent->videoQueue->id);

        return true;
    }

}
