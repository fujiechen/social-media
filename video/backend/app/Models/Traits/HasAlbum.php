<?php

namespace App\Models\Traits;

use App\Models\Album;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasAlbum
{
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
