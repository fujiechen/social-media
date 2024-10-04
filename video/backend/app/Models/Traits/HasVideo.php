<?php

namespace App\Models\Traits;

use App\Models\Media;
use App\Models\Video;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasVideo
{
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}
