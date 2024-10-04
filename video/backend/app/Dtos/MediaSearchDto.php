<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class MediaSearchDto extends DataTransferObject
{
    public ?int $mediaId = null;
    public ?int $userId = null;
    public ?int $mediaUserId = null;
    public array $mediaableTypes = [];
    public ?int $mediaableId = null;
    public ?string $mediaSearchText = null;
    public ?int $actorId = null;
    public ?int $categoryId = null;
    public array $tagIds = [];

    public ?string $actorName = null;
    public ?string $categoryName = null;
    public ?string $tagName = null;
    public ?string $nickName = null;
    public array $orderBys = [];
}
