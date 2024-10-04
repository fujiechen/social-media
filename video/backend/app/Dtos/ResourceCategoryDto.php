<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ResourceCategoryDto extends DataTransferObject
{
    public int $categoryId = 0;
    public string $name;
}
