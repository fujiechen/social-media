<?php

namespace App\Events;

use App\Models\AlbumActor;
use Illuminate\Queue\SerializesModels;

class AlbumActorSavedEvent
{
    use SerializesModels;

    public AlbumActor $albumActor;

    public function __construct(AlbumActor $albumActor) {
        $this->albumActor = $albumActor;
    }
}
