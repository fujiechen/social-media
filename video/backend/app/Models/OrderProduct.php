<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
 * @property Carbon $created_at
 * @property Carbon $updated_at
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
        'product_json' => 'array'
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

    public function getValidUntilAtDaysAttribute(): ?int {
        $validDays = $this->product->frequency_as_extend_days;
        if (!$validDays) {
            return null;
        }

        $now = Carbon::now();
        $createdAt = $this->created_at;
        $expiredAt = $this->created_at->addDays($this->product->frequency_as_extend_days);

        $days = $expiredAt->diffInDays($createdAt);

        if ($now > $expiredAt) {
            return - $days - 1;
        } else {
            return $days + 1;
        }
    }
}
