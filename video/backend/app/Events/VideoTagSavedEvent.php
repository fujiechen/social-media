<?php

namespace App\Events;

use App\Models\VideoTag;
use Illuminate\Queue\SerializesModels;

class VideoTagSavedEvent
{
    use SerializesModels;

    public VideoTag $videoTag;

    public function __construct(VideoTag $videoTag) {
        $this->videoTag = $videoTag;
    }
}
