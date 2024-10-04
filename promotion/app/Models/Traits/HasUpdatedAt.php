<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

/**
 * @property string $updated_at_formatted
 */
trait HasUpdatedAt
{
    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at->format('Y-m-d H:i:s');
    }
}
