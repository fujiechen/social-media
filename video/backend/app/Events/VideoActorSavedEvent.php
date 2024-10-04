<?php

namespace App\Events;

use App\Models\VideoActor;
use Illuminate\Queue\SerializesModels;


class VideoActorSavedEvent
{
    use SerializesModels;

    public VideoActor $videoActor;

    public function __construct(VideoActor $videoActor) {
        $this->videoActor = $videoActor;
    }
}
