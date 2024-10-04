<?php

namespace App\Events;

use App\Services\SeriesService;

class SeriesDeletedEventHandler
{
    private SeriesService $seriesService;

    public function __construct(SeriesService $seriesService) {
        $this->seriesService = $seriesService;
    }

    public function handle(SeriesDeletedEvent $event): void {
        $this->seriesService->postDeleted($event->series);
    }
}
