<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class CategoryDto extends DataTransferObject
{
    public string $type;
    public int $categoryId = 0;
    public int $priority = 0;
    public string $name;
    public array $resourceCategoryIds = [];
    public ?FileDto $avatarFileDto;
}
