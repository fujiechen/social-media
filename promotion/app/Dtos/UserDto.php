<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class UserDto extends DataTransferObject
{
    public ?int $userId;
    public ?string $accessToken = null;
    public string $username;
    public string $password;
    public string $nickname;
    public string $email;
    public ?string $language;
    public ?int $userShareId = null;
    public array $roleIds = [];
}
