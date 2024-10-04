<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ResourceActorDto extends DataTransferObject
{
    public int $resourceActorId = 0;
    public string $name;
    public string $country = '';
}
