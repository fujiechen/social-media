<?php

namespace App\Events;

use App\Dtos\CategoryUserDto;
use App\Mail\CompleteOrderEmail;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Services\CategoryUserService;
use App\Services\UserPayoutService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderSavedEventHandler implements ShouldQueue
{
    private UserPayoutService $userPayoutService;
    private CategoryUserService $categoryUserService;

    public function __construct(UserPayoutService $userPayoutService, CategoryUserService $categoryUserService) {
        $this->userPayoutService = $userPayoutService;
        $this->categoryUserService = $categoryUserService;
    }

    public function handle(OrderSavedEvent $orderSavedEvent): void {
        if ($orderSavedEvent->order->status == Order::STATUS_PENDING) {
            return;
        }

        $orderId = $orderSavedEvent->order->id;
        Log::info('adding category for user with order id ' . $orderId);

        DB::transaction(function() use ($orderId) {
            /**
             * @var Order $order
             */
            $order = Order::find($orderId);
            $userId = $order->user_id;

            /**
             * @var OrderProduct $orderProduct
             */
            foreach ($order->orderProducts as $orderProduct) {
                $categoryId = $orderProduct->product->category_id;
                $categoryUser = $this->categoryUserService->findCategoryUser($categoryId, $userId);
                if (empty($categoryUser)) {
                    $validUtilAt = Carbon::now();
                } else {
                    $validUtilAt = $categoryUser->valid_until_at;
                }

                $this->categoryUserService->updateOrCreateCategoryUser(new CategoryUserDto([
                    'userId' => $userId,
                    'categoryId' => $categoryId,
                    'validUntilAt' => $validUtilAt
                        ->addDays($orderProduct->qty * $orderProduct->product->frequency_as_extend_days)
                ]));

                Log::info('created order ' . $orderId . ' for category ' . $categoryId);
            }

            Mail::to($order->user->email)->queue(new CompleteOrderEmail($order));
        });

        $this->userPayoutService->processCompletedOrderPayout($orderId);
    }
}
