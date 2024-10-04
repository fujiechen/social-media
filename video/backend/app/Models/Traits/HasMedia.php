<?php

namespace App\Models\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasMedia
{
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
