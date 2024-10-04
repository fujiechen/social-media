<?php

namespace App\Models\Traits;


/**
 * @property string $created_at_formatted
 */
trait HasCreatedAt
{
    public function getCreatedAtFormattedAttribute(): string
    {
        if ($this->created_at) {
            return $this->created_at->format('Y-m-d H:i:s');
        }
        return '';
    }
}
