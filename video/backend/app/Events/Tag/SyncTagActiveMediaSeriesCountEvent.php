<?php

namespace App\Events\Tag;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class SyncTagActiveMediaSeriesCountEvent
{
    use SerializesModels;

    public Media $media;
    public bool $isActive;

    public function __construct(Media $media, bool $isActive)
    {
        $this->media = $media;
        $this->isActive = $isActive;
    }
}
