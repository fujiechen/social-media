<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class ActorDto extends DataTransferObject
{
    public string $type;
    public int $actorId = 0;
    public string $name;
    public ?string $description = null;
    public int $priority = 0;
    public ?string $country = null;
    public array $resourceActorIds = [];
    public ?FileDto $avatarFileDto;
}
