<?php

namespace App\Events\Actor;

use App\Models\Actor;
use Illuminate\Queue\SerializesModels;

class AddActorViewCountEvent
{
    use SerializesModels;

    public Actor $actor;

    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
    }
}
