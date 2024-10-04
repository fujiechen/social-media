<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class UnionUserDto extends DataTransferObject
{
    public ?int $unionUserId = null;
    public ?int $userId = null;
    public string $username;
    public string $email;
    public string $nickname;
    public string $password;
    public ?string $language = null;
}
