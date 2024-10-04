<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasUpdatedAt
{
    public function getUpdatedAtFormattedAttribute(): string
    {
        return Carbon::parse($this->updated_at)->format('Y-m-d H:i:s');
    }
}
