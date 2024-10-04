<?php

namespace App\Events;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserPayout;

class PaymentCreatedEventHandler
{
    public function handle(PaymentCreatedEvent $event): void {
        $payment = $event->payment;

        if (!empty($payment->order_id) && $payment->status == Payment::STATUS_SUCCESSFUL) {
            $order = $payment->order;
            $order->status = Order::STATUS_COMPLETED;
            $order->save();
        }

        if (!empty($payment->user_payout_id) && $payment->status == Payment::STATUS_SUCCESSFUL) {
            $userPayout = $payment->userPayout;
            $userPayout->status = UserPayout::STATUS_COMPLETED;
            $userPayout->save();
        }

    }
}
