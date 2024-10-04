<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class UserShareDto extends DataTransferObject
{
    public ?int $userShareId;
    public int $userId;
    public ?string $shareableType;
    public ?int $shareableId;
    public string $url;
}
