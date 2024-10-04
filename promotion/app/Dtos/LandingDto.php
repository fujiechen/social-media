<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class LandingDto extends DataTransferObject
{
    public string $url;
    public string $signature;
    public int $landingTemplateId;
    public int $accountId;
    public ?int $postId = null;
    public string $ip;
    public bool $redirect = false;
}
