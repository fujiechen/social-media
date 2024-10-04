<?php

namespace App\Models\Traits;


/**
 * @property string $updated_at_formatted
 */
trait HasUpdatedAt
{
    public function getUpdatedAtFormattedAttribute(): string
    {
        if ($this->updated_at) {
            return $this->updated_at->format('Y-m-d H:i:s');
        }
        return '';
    }
}
