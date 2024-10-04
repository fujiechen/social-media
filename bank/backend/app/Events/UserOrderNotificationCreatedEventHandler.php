<?php

namespace App\Events;

use App\Http\Resources\UserOrderResource;
use App\Models\UserOrder;
use App\Models\UserOrderNotification;
use App\Models\UserOrderPayment;
use App\Services\OrderNotifiers\OrderBotService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UserOrderNotificationCreatedEventHandler implements ShouldQueue
{
    private OrderBotService $orderBotService;

    public function __construct(OrderBotService $orderBotService) {
        $this->orderBotService = $orderBotService;
    }

    public function handle(UserOrderNotificationCreatedEvent $event): bool {
        /**
         * @var UserOrderNotification $userOrderNotification
         */
        $userOrderNotification = UserOrderNotification::find($event->userOrderNotification->id);
        Log::info('UserOrderNotificationCreatedEventHandler get user order notification id:' . $userOrderNotification->id);
        $userOrder = $userOrderNotification->userOrder;
        $orderNotifier = $userOrderNotification->orderNotifier;

        /**
         * Call order notifier webhook User Order is Deposit
         */
        if ($userOrder->type === UserOrder::TYPE_DEPOSIT
            && !empty($userOrder->userOrderPayments)
            && $userOrder->userOrderPayments->contains(function ($payment) {
                return isset($payment['action']) && $payment['action'] === UserOrderPayment::ACTION_WEBHOOK;
            })
        ) {
            Log::info('UserOrderNotificationCreatedEventHandler user order notification id:' . $userOrderNotification->id . ' Get webhook Deposit user order and call to order bot');
            return $this->orderBotService->createOrderBotNotification(
                $orderNotifier->id,
                $orderNotifier->notifier_url,
                $orderNotifier->access_token,
                $userOrder->id,
                $userOrder->status,
                (array)(new UserOrderResource($userOrder)),
            );
        }

        return true;
    }

}
