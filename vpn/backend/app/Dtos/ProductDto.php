<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ProductDto extends DataTransferObject
{
    public int $productId = 0;
    public string $name;
    public string $description;
    public string $currencyName;
    public float $unitPrice = 0;
    public string $frequency;
    public FileDto $thumbnailFileDto;
    public array $imageFileDtos = [];
    public int $categoryId;
    public ?int $orderNumAllowance;
}
