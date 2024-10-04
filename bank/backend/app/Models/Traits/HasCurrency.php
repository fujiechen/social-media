<?php

namespace App\Models\Traits;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCurrency
{
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
