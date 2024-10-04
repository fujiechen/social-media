<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ServerUserDto extends DataTransferObject
{
    public int $serverId = 0;
    public int $userId = 0;
    public string $radiusUuid = '';
    public string $radiusUsername = '';
    public string $radiusPassword = '';
}
