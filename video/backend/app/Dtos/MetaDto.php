<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class MetaDto extends DataTransferObject
{
    public string $meta_key;
    public string $meta_value;
}
