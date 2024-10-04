<?php

namespace App\Events;

use App\Models\VideoQueue;
use Illuminate\Queue\SerializesModels;

class VideoQueueUpdatedEvent
{
    use SerializesModels;

    public VideoQueue $videoQueue;

    public function __construct(VideoQueue $videoQueue) {
        $this->videoQueue = $videoQueue;
    }
}
