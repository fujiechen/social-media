<?php

namespace App\Events\Category;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class SyncCategoryActiveMediaSeriesCountEvent
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
