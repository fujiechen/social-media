<?php

namespace App\Models\Traits;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasProduct
{
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
