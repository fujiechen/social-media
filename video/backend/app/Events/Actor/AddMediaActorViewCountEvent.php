<?php

namespace App\Events\Actor;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class AddMediaActorViewCountEvent
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
