<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceCategoryRemoveCategoryEvent
{
    use SerializesModels, Dispatchable;

    public int $resourceCategoryId;
    public int $removedCategoryId;

    public function __construct(int $resourceCategoryId, int $removedCategoryId) {
        $this->resourceCategoryId = $resourceCategoryId;
        $this->removedCategoryId = $removedCategoryId;
    }
}
