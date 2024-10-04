<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class TargetUrlDto extends DataTransferObject
{
    public int $id = 0;
    public string $name;
    public string $url;
    public string $status;
    public ?FileDto $qrFileDto = null;
}
