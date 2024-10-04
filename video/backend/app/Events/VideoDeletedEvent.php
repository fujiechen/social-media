<?php

namespace App\Events;

use App\Models\Video;
use Illuminate\Queue\SerializesModels;

class VideoDeletedEvent
{
    use SerializesModels;

    public Video $video;

    public function __construct(Video $video) {
        $this->video = $video;
    }
}
