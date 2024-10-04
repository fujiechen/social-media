<?php

namespace App\Events;

use App\Models\UserOrderPayment;
use Illuminate\Queue\SerializesModels;

class UserOrderPaymentCreatedEvent
{
    use SerializesModels;

    public UserOrderPayment $userOrderPayment;

    public function __construct(UserOrderPayment $userOrderPayment) {
        $this->userOrderPayment = $userOrderPayment;
    }
}
