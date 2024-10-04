<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceTagRemoveTagEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceTagId;
    public int $removedTagId;

    public function __construct(int $resourceTagId, int $removedTagId) {
        $this->resourceTagId = $resourceTagId;
        $this->removedTagId = $removedTagId;
    }
}
