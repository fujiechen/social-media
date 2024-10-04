<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentCreatedEvent
{
    use SerializesModels;

    public Payment $payment;

    public function __construct(Payment $payment) {
        $this->payment = $payment;
    }
}
