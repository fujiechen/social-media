<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class OrderSearchDto extends DataTransferObject
{
    public ?int $orderId = null;
    public ?int $userId = null;
    public ?int $productId = null;
    public ?string $status = null;
    public ?int $parentUserId = null;
}
