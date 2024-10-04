<?php

namespace App\Events;

use App\Models\Album;
use Illuminate\Queue\SerializesModels;

class AlbumDeletedEvent
{
    use SerializesModels;

    public Album $album;

    public function __construct(Album $album) {
        $this->album = $album;
    }
}
