<?php

namespace App\Models;

use App\Events\UserPayoutSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $user_id
 * @property User $user
 * @property int $order_product_id
 * @property OrderProduct $orderProduct
 * @property string $type
 * @property string $status
 * @property string $currency_name
 * @property int $amount_cents
 * @property float $amount
 * @property string $comment
 * @property Collection|Payment[] $payments
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property ?string $order_user_nickname
 */
class UserPayout extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;
    use HasUser;

    protected $fillable = [
        'type',
        'status',
        'user_id',
        'order_product_id',
        'currency_name',
        'amount_cents',
        'comment',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'amount',
        'order_user_nickname',
        'status_name',
    ];

    protected $dispatchesEvents = [
        'saved' => UserPayoutSavedEvent::class,
    ];

    const TYPE_EARNING = 'earning';
    const TYPE_COMMISSION = 'commission';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    public function getAmountAttribute(): float {
        return $this->amount_cents / 100;
    }

    public function getOrderUserNicknameAttribute(): ?string {
        return $this->orderProduct?->order->user->nickname;
    }

    public function orderProduct(): BelongsTo {
        return $this->belongsTo(OrderProduct::class);
    }

    public function payments(): HasMany {
        return $this->hasMany(Payment::class);
    }

    public function getStatusNameAttribute(): string {
        if($this->status == self::STATUS_PENDING) return '待发放';
        if($this->status == self::STATUS_COMPLETED) return '已发放';
        return '';
    }
}
