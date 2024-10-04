<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

/**
 * @property string $created_at_formatted
 */
trait HasCreatedAt
{
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
