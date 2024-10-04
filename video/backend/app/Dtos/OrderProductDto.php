<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class OrderProductDto extends DataTransferObject
{
    public int $productId;
    public int $qty;
}
