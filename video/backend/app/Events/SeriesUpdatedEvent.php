<?php

namespace App\Events;

use App\Models\Series;
use Illuminate\Queue\SerializesModels;

class SeriesUpdatedEvent
{
    use SerializesModels;

    public Series $series;

    public function __construct(Series $series) {
        $this->series = $series;
    }
}
