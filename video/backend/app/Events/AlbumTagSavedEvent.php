<?php

namespace App\Events;

use App\Models\AlbumTag;
use Illuminate\Queue\SerializesModels;

class AlbumTagSavedEvent
{
    use SerializesModels;

    public AlbumTag $albumTag;

    public function __construct(AlbumTag $albumTag) {
        $this->albumTag = $albumTag;
    }
}
