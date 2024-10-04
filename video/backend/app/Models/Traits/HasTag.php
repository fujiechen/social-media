<?php

namespace App\Models\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTag
{
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
