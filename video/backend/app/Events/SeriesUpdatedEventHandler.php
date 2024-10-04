<?php

namespace App\Events;

use App\Services\SeriesService;

class SeriesUpdatedEventHandler
{
    private SeriesService $seriesService;

    public function __construct(SeriesService $seriesService) {
        $this->seriesService = $seriesService;
    }

    public function handle(SeriesUpdatedEvent $event):void {
        $this->seriesService->postUpdated($event->series->id);
    }
}
