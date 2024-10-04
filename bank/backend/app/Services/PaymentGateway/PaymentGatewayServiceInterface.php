<?php

namespace App\Services\PaymentGateway;

use App\Dtos\PaymentGatewayIntent;
use App\Dtos\PaymentGatewayWebhook;

interface PaymentGatewayServiceInterface
{
    public function createPaymentIntent(PaymentGatewayIntent $paymentGatewayIntent): PaymentGatewayIntent;

    public function processPaymentWebhook(PaymentGatewayWebhook $paymentGatewayWebhook): PaymentGatewayWebhook;
}
