<?php

namespace App\Dtos;

use App\Models\Product;
use App\Utils\DataTransferObject;

class ProductDto extends DataTransferObject
{
    public int $productId = 0;
    public string $type;
    public ?int $userId = null;
    public string $name;
    public ?string $description = null;
    public string $currencyName;
    public float $unitPrice = 0;
    public string $frequency = Product::ONETIME;
    public FileDto $thumbnailFileDto;
    public array $imageFileDtos = [];
    public ?string $fileType;
    public ?int $orderNumAllowance;
    public bool $isActive = true;

    public static function create(array $productDtoArray): ProductDto {
        if ($productDtoArray['type'] == Product::TYPE_MEMBERSHIP) {
            return new MembershipProductDto($productDtoArray);
        } else if ($productDtoArray['type'] == Product::TYPE_SUBSCRIPTION) {
            return new SubscriptionProductDto($productDtoArray);
        } if ($productDtoArray['type'] == Product::TYPE_MEDIA) {
            return new PurchaseProductDto($productDtoArray);
        }
        return new ProductDto($productDtoArray);
    }
}
