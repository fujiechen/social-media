<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceTagAddTagEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceTagId;

    public function __construct(int $resourceTagId) {
        $this->resourceTagId = $resourceTagId;
    }
}
