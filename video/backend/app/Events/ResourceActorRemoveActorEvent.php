<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceActorRemoveActorEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceActorId;
    public int $removedActorId;

    public function __construct(int $resourceActorId, int $removedActorId) {
        $this->resourceActorId = $resourceActorId;
        $this->removedActorId = $removedActorId;
    }
}
