<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property array $product_json
 * @property int $unit_cents
 * @property float $unit_amount
 * @property string $currency_name
 * @property int $qty
 * @property Product $product
 * @property Order $order
 */
class OrderProduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_json',
        'currency_name',
        'unit_cents',
        'qty',
    ];

    protected $with = [
        'product',
    ];

    protected $casts = [
        'product_json' => 'array',
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'unit_amount',
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function getUnitAmountAttribute(): float {
        return $this->unit_cents / 100;
    }
}
