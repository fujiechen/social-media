<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class OrderDto extends DataTransferObject
{
    public ?int $orderId = null;
    public int $userId;
    public string $status;
    public array $orderProductDtos = [];
}
