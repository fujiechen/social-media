<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ContentTypeDto extends DataTransferObject
{
    public int $id = 0;
    public string $name;
    public string $description;
    public string $fileType = 'upload';
    public array $fileDtos = [];
}
