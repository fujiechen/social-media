<?php

namespace App\Events\Tag;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class AddMediaTagViewCountEvent
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
