<?php

namespace App\Events;

use App\Models\UserOrder;
use App\Models\UserOrderPayment;
use App\Services\UserOrderService;
use Illuminate\Support\Facades\Log;

class UserOrderPaymentCreatedEventHandler
{
    private UserOrderService $userOrderService;

    public function __construct(UserOrderService $userOrderService) {
        $this->userOrderService = $userOrderService;
    }

    public function handle(UserOrderPaymentCreatedEvent $event): bool {
        /**
         * @var UserOrderPayment $userOrderPayment
         */
        $userOrderPayment = UserOrderPayment::find($event->userOrderPayment->id);

        if ($userOrderPayment->userOrder->status == UserOrder::STATUS_PENDING) {
            Log::info('received pending status of user order and valid user_order_payment_id ' . $userOrderPayment->id);

            if ($userOrderPayment->action === UserOrderPayment::ACTION_CREATE) {
                if ($userOrderPayment->status == UserOrderPayment::STATUS_FAILED) {
                    Log::info('update user order to failed, user_order_id ' . $userOrderPayment->user_order_id);
                    $this->userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_FAILED, $userOrderPayment->user_order_id);
                } else {
                    Log::info('ignore create action');
                }
                return true;
            }

            if ($userOrderPayment->status == UserOrderPayment::STATUS_SUCCESSFUL) {
                Log::info('update user order to success, user_order_id ' . $userOrderPayment->user_order_id);
                $this->userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_SUCCESSFUL, $userOrderPayment->user_order_id);
            } else if ($userOrderPayment->status == UserOrderPayment::STATUS_FAILED) {
                Log::info('update user order to failed, user_order_id ' . $userOrderPayment->user_order_id);
                $this->userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_FAILED, $userOrderPayment->user_order_id);
            }
        }

        return true;
    }

}
