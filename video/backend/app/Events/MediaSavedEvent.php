<?php

namespace App\Events;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class MediaSavedEvent
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media) {
        $this->media = $media;
    }
}
