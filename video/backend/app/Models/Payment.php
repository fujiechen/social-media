<?php

namespace App\Models;

use App\Events\PaymentCreatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $total_cents
 * @property string $status
 * @property ?int $user_payout_id
 * @property ?int $order_id
 * @property string $currency_name
 * @property int $amount_cents
 * @property float $amount
 * @property array $request
 * @property array $response
 * @property ?Order $order
 * @property ?UserPayout $userPayout
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 */
class Payment extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'user_payout_id',
        'order_id',
        'currency_name',
        'amount_cents',
        'request',
        'response',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'amount',
    ];

    protected $dispatchesEvents = [
        'created' => PaymentCreatedEvent::class,
    ];

    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function userPayout(): BelongsTo {
        return $this->belongsTo(UserPayout::class);
    }

    public function getAmountAttribute(): float {
        return $this->amount_cents / 100;
    }
}
