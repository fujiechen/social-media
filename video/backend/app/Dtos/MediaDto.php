<?php

namespace App\Dtos;

use App\Models\Media;
use App\Utils\DataTransferObject;

class MediaDto extends DataTransferObject
{
    public int $mediaId = 0;
    public ?int $parentMediaId = null;
    public int $userId;
    public string $mediaableType;
    public array $mediaPermissions;
    public ?int $videoId;
    public ?int $seriesId;
    public ?int $albumId;
    public string $name = '';
    public ?string $description = null;
    public array $mediaRoleIds = [];
    public ?string $mediaProductCurrencyName = null;
    public ?float $mediaProductPrice = null;
    public int $status = Media::STATUS_DRAFT;
}
