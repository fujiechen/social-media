<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceCategoryAddCategoryEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceCategoryId;

    public function __construct(int $resourceCategoryId) {
        $this->resourceCategoryId = $resourceCategoryId;
    }
}
