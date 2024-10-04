<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class LandingTemplateDto extends DataTransferObject
{
    public int $id = 0;
    public string $name;
    public string $description;
    public string $landingHtml;
    public int $redirectTypeId;
    public int $targetUrlId;
    public int $landingDomainId;
    public string $status;
    public ?FileDto $bannerFileDto = null;
}
