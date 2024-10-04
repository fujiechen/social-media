<?php

namespace App\Events;

use App\Exceptions\IllegalArgumentException;
use App\Models\UserPayout;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserPayoutSavedEventHandler implements ShouldQueue
{
    public PaymentService $paymentService;

    public function __construct(PaymentService $paymentService) {
        $this->paymentService = $paymentService;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function handle(UserPayoutSavedEvent $event): void {
        $userPayout = $event->userPayout;

        if ($userPayout->status == UserPayout::STATUS_PENDING) {
            $this->paymentService->createUserPayoutPayment($userPayout->id);
        }
    }
}
