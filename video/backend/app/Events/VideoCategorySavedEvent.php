<?php

namespace App\Events;

use App\Models\VideoCategory;
use Illuminate\Queue\SerializesModels;

class VideoCategorySavedEvent
{
    use SerializesModels;

    public VideoCategory $videoCategory;

    public function __construct(VideoCategory $videoCategory) {
        $this->videoCategory = $videoCategory;
    }
}
