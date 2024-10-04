<?php

namespace App\Models;

use App\Events\OrderSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $status
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property int $total_cents
 * @property float $total_amount
 * @property string $currency_name
 * @property Collection|OrderProduct[] $orderProducts
 * @property Collection|Payment[] $payments
 * @property Collection|Product[] $products
 * @property Collection|String[] $product_ids
 * @property string $status_name
 */
class Order extends Model
{
    use HasUser;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'user_id',
        'status',
        'currency_name',
        'total_cents',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'orderProducts',
        'products',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'product_ids',
        'product_names',
        'total_amount',
        'status_name',
    ];

    protected $dispatchesEvents = [
        'saved' => OrderSavedEvent::class,
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    public function orderProducts(): HasMany {
        return $this->hasMany(OrderProduct::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }

    public function products(): HasManyThrough {
        return $this->hasManyThrough(Product::class, OrderProduct::class,
            'order_id', 'id', 'id', 'product_id');
    }

    public function getProductIdsAttribute(): Collection
    {
        return $this->products->pluck('id');
    }

    public function getProductNamesAttribute(): Collection
    {
        return $this->products->pluck('name');
    }

    public function getTotalAmountAttribute(): float {
        return $this->total_cents / 100;
    }

    public function getStatusNameAttribute(): string {
        if($this->status == self::STATUS_PENDING) return '待支付';
        if($this->status == self::STATUS_COMPLETED) return '已完成';
        return '';
    }
}
