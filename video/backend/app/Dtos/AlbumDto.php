<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class AlbumDto extends DataTransferObject
{
    public int $albumId = 0;
    public string $type;
    public string $name;
    public string $description;
    public ?string $resourceAlbumId;
    public ?FileDto $thumbnailFileDto = null;
    public ?FileDto $downloadFileDto = null;

    /**
     * @var FileDto[]
     */
    public array $imageFileDtos;

    public array $tagIds = [];
    public array $categoryIds = [];
    public array $actorIds = [];

    /**
     * @var MetaDto[] $metaJson
     */
    public array $metaJson = [];
}
