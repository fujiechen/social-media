<?php

namespace App\Services;

use App\Models\UserOrder;
use App\Models\UserOrderNotification;
use Illuminate\Support\Facades\Log;

class UserOrderNotificationService
{
    public function __construct()
    {
    }

    public function createUserOrderNotification(int $userOrderId): void
    {
        $userOrder = UserOrder::find($userOrderId);
        if (!isset($userOrder->meta_json[UserOrderNotification::META_JSON_KEY_ORDER_NOTIFIER_ID]) ||
            !isset($userOrder->meta_json[UserOrderNotification::META_JSON_KEY_CALLBACK_PAYLOAD])
        ) {
            Log::error('Cannot find required info from user order id: ' . $userOrderId);
            return;
        }

        UserOrderNotification::create([
            'user_order_id' => $userOrderId,
            'order_notifier_id' => $userOrder->meta_json[UserOrderNotification::META_JSON_KEY_ORDER_NOTIFIER_ID],
            'payload' => $userOrder->meta_json[UserOrderNotification::META_JSON_KEY_CALLBACK_PAYLOAD],
            'status' => $userOrder->status,
        ]);
    }
}
