<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;
use Illuminate\Support\Carbon;

class CategoryUserDto extends DataTransferObject
{
    public int $categoryId = 0;
    public int $userId = 0;
    public ?Carbon $validUntilAt = null;
}
