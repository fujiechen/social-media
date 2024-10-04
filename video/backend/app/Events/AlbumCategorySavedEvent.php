<?php

namespace App\Events;

use App\Models\AlbumCategory;
use Illuminate\Queue\SerializesModels;

class AlbumCategorySavedEvent
{
    use SerializesModels;

    public AlbumCategory $albumCategory;

    public function __construct(AlbumCategory $albumCategory) {
        $this->albumCategory = $albumCategory;
    }
}
