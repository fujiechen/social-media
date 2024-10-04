<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class ProductSearchDto extends DataTransferObject
{
    public ?int $userId = null;
    public ?string $type = null;
    public ?string $productUserType = null;
    public ?string $currencyName = null;
    public ?int $mediaId = null;
}
