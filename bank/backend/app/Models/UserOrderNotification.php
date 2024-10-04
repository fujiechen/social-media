<?php

namespace App\Models;

use App\Events\UserOrderNotificationCreatedEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $access_token
 * @property string $notifier_url
 * @property int $max_retry_times
 * @property-read UserOrder $userOrder
 * @property-read OrderNotifier $orderNotifier
 */
class UserOrderNotification extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAIL = 'fail';
    const META_JSON_KEY_ORDER_NOTIFIER_ID = 'order_notifier_id';
    const META_JSON_KEY_CALLBACK_PAYLOAD = 'callback_payload';

    public $timestamps = true;

    protected $fillable = [
        'user_order_id',
        'order_notifier_id',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    protected $dispatchesEvents = [
        'created' => UserOrderNotificationCreatedEvent::class
    ];

    public function userOrder(): BelongsTo
    {
        return $this->belongsTo(UserOrder::class);
    }

    public function orderNotifier(): BelongsTo
    {
        return $this->belongsTo(OrderNotifier::class);
    }
}
