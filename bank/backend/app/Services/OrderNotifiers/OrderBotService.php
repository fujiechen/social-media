<?php

namespace App\Services\OrderNotifiers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderBotService
{
    public function __construct()
    {
    }

    public function createOrderBotNotification(
        int $userOrderNotificationId,
        string $orderNotifierUrl,
        string $orderNotifierAccessToken,
        string $userOrderId,
        string $userOrderStatus,
        array $payload,
    ): bool
    {
        $request = [
            'api_key' => $orderNotifierAccessToken,
            'user_order_id' => $userOrderId,
            'user_order_status' => $userOrderStatus,
            'payload' => $payload,
        ];

        Log::info('createOrderBotNotification calling to order notifier url:' .$orderNotifierUrl. ' with user order id:' . $userOrderId);
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json'
            ])->post($orderNotifierUrl, $request);

            if ($response->successful()) {
                Log::info('createOrderBotNotification success call to order notifier url:' .$orderNotifierUrl. ' with user order id:' . $userOrderId);
                return true;
            } else {
                Log::error('createOrderBotNotification failed call to order notifier url:' .$orderNotifierUrl. ' with user order id:' . $userOrderId);
                Log::error('createOrderBotNotification Cannot call to Order bot for user order notification ID:' . $userOrderNotificationId);

                // TODO: send email
                return false;
            }
        } catch (\Exception $exception) {
            Log::error('createOrderBotNotification failed call to order notifier url:' .$orderNotifierUrl. ' with user order id:' . $userOrderId. ' Exception:' . $exception->getMessage());
            Log::error('createOrderBotNotification Cannot call to Order bot for user order notification ID:' . $userOrderNotificationId);
            return false;
        }
    }
}
