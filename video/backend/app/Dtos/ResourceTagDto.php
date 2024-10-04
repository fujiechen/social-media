<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ResourceTagDto extends DataTransferObject
{
    public int $resourceTagId = 0;
    public string $name;
}
