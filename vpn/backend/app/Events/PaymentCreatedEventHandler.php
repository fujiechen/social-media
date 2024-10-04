<?php

namespace App\Events;

use App\Mail\CompleteUserPayoutEmail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UserPayout;
use Illuminate\Support\Facades\Mail;

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
            UserPayout::withoutEvents(function() use ($userPayout) {
                $userPayout->status = UserPayout::STATUS_COMPLETED;
                $userPayout->save();
            });
            Mail::to($userPayout->user->email)->queue(new CompleteUserPayoutEmail($userPayout));
        }
    }
}
