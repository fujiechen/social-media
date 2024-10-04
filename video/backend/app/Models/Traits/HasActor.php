<?php

namespace App\Models\Traits;

use App\Models\Actor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasActor
{
    public function actor(): BelongsTo
    {
        return $this->belongsTo(Actor::class);
    }
}
