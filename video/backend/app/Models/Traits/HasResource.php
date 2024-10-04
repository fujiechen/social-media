<?php

namespace App\Models\Traits;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasResource
{
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
