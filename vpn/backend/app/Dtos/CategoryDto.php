<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class CategoryDto extends DataTransferObject
{
    public int $categoryId = 0;
    public string $name;
    public string $description;
    public array $tags = [];
    public array $highlights = [];
    public FileDto $thumbnailFileDto;
}
