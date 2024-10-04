<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Product;
use App\Services\UserPayoutService;
use App\Services\UserRoleService;
use App\Services\UserFollowingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class OrderSavedEventHandler implements ShouldQueue
{
    private UserFollowingService $userFollowingService;
    private UserRoleService $userRoleService;
    private UserPayoutService $userPayoutService;

    public function __construct(UserFollowingService $userFollowingService, UserRoleService $userRoleService, UserPayoutService $userPayoutService) {
        $this->userFollowingService = $userFollowingService;
        $this->userRoleService = $userRoleService;
        $this->userPayoutService = $userPayoutService;
    }

    public function handle(OrderSavedEvent $orderSavedEvent): void {
        if ($orderSavedEvent->order->status != Order::STATUS_COMPLETED) {
            return;
        }

        $orderId = $orderSavedEvent->order->id;

        DB::transaction(function() use ($orderId) {
            /**
             * @var Order $order
             */
            $order = Order::find($orderId);

            $user = $order->user;

            /**
             * @var Product $product
             */
            foreach ($order->products as $product) {
                if ($product->type == Product::TYPE_MEMBERSHIP) {
                    $this->userRoleService->updateOrCreateUserRole($user->id, $product->role_id, $product->frequency_as_extend_days);
                } else if ($product->type == Product::TYPE_SUBSCRIPTION) {
                    $this->userFollowingService->addSubscription($user->id, $product->publisher_user_id, $product->frequency_as_extend_days);
                }
            }
        });

        $this->userPayoutService->processCompletedOrderPayout($orderId);
    }
}
