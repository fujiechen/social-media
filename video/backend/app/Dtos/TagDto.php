<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class TagDto extends DataTransferObject
{
    public int $tagId = 0;
    public string $name;
    public int $priority = 0;
    public array $resourceTagIds = [];
}
