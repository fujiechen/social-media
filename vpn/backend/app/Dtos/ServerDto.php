<?php

namespace App\Dtos;

class ServerDto extends FileDto
{
    public int $serverId = 0;
    public string $name;
    public string $type;
    public string $ip;
    public string $countryCode;
    public int $categoryId = 0;
    public ?string $adminUrl;
    public ?string $adminUsername;
    public ?string $adminPassword;
    public ?FileDto $adminPemFileDto;
    public ?FileDto $ovpnFileDto;
    public string $description;
    public string $apiUrl;
    public string $apiKey;
    public string $apiSecret;
    public string $ipSecSharedKey;
}
