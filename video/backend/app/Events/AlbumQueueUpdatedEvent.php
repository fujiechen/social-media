<?php

namespace App\Events;

use App\Models\AlbumQueue;
use Illuminate\Queue\SerializesModels;

class AlbumQueueUpdatedEvent
{
    use SerializesModels;

    public AlbumQueue $albumQueue;

    public function __construct(AlbumQueue $albumQueue) {
        $this->albumQueue = $albumQueue;
    }
}
