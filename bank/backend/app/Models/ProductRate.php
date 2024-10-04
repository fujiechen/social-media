<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property int $rate
 * @property float $value
 * @property-read Product $product
 */
class ProductRate extends Model
{
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
        'product_id',
        'rate',
        'value',
        'created_at'
    ];

    protected $with = [
        'product',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
