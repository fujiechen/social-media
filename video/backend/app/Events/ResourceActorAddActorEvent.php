<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceActorAddActorEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceActorId;

    public function __construct(int $resourceActorId) {
        $this->resourceActorId = $resourceActorId;
    }
}
