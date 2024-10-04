<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasCreatedAt
{
    public function getCreatedAtFormattedAttribute(): string
    {
        return Carbon::parse($this->created_at)->format('Y-m-d H:i:s');
    }
}
