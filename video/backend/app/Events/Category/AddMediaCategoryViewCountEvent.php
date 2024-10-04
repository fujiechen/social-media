<?php

namespace App\Events\Category;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class AddMediaCategoryViewCountEvent
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
