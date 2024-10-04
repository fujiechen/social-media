<?php

namespace App\Models;

use App\Events\UserOrderPaymentCreatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_order_id
 * @property UserOrder $userOrder
 * @property int $payment_gateway_id
 * @property PaymentGateway $paymentGateway
 * @property string $action
 * @property int $amount
 * @property float $amount_in_dollar
 * @property string $stripe_intent_id
 * @property string $stripe_intent_client_secret
 * @property string $request
 * @property string $response
 * @property string $status
 */
class UserOrderPayment extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    public $timestamps = true;

    protected $fillable = [
        'user_order_id',
        'payment_gateway_id',
        'action',
        'amount',
        'stripe_intent_id',
        'stripe_intent_client_secret',
        'status',
        'request',
        'response',
    ];

    protected $with = [
        'paymentGateway',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'amount_in_dollar',
    ];

    protected $dispatchesEvents = [
        'created' => UserOrderPaymentCreatedEvent::class
    ];

    const ACTION_CREATE = 'create';
    const ACTION_RETRIEVE = 'retrieve';
    const ACTION_WEBHOOK = 'webhook';

    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';

    public function userOrder(): BelongsTo {
        return $this->belongsTo(UserOrder::class);
    }

    public function paymentGateway(): BelongsTo {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function getAmountInDollarAttribute(): float {
        return $this->amount / 100;
    }

}
