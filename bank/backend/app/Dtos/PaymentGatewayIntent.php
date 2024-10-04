<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class PaymentGatewayIntent extends DataTransferObject
{
    public int $userOrderId;
    public ?string $paymentIntentId;
    public string $paymentMethod;
    public int $amount; // in cent
    public string $currencyName;
    public ?string $paymentGatewaySecret;
    public ?string $clientSecret;
    public ?string $userOrderStatus;
    public array $response = [];
    public ?string $webhookUrl;
    public ?string $callbackUrl;
    public ?string $endpointUrl;
}
