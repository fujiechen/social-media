<?php

namespace App\Events;

use App\Models\Album;
use Illuminate\Queue\SerializesModels;

class AlbumUpdatedEvent
{
    use SerializesModels;

    public Album $album;

    public function __construct(Album $album) {
        $this->album = $album;
    }
}
