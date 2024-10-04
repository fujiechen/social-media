<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class PaymentGatewayWebhook extends DataTransferObject
{
    public string $paymentIntentId;
    public ?int $userOrderId;
    public string $payload;
    public string $signature;
    public string $status;
    public ?string $webhookSecret;
    public int $amount;
    public array $response = [];
    public ?string $paymentGatewaySecret;
}
