<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

abstract class MediaQueueDto extends DataTransferObject
{
    public int $userId;
    public string $mediaRoleIds;
    public string $mediaType;
}
