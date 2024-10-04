<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class AppDto extends DataTransferObject
{
    public int $appId = 0;
    public string $name;
    public string $description;
    public string $url;
    public int $appCategoryId;
    public bool $isHot = false;
    public FileDto $iconFileDto;
}
