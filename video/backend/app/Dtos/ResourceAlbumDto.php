<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class ResourceAlbumDto extends DataTransferObject
{
    public ?int $resourceAlbumId = null;
    public int $resourceId;
    public string $resourceAlbumUrl;
    public string $name;
    public ?string $description = null;    public ?FileDto $thumbnailFileDto = null;

    public ?FileDto $downloadFileDto = null;

    /**
     * @var FileDto[] $resourceAlbumFileDtos
     */
    public array $resourceAlbumFileDtos = [];

    /**
     * @var ResourceTagDto[] $resourceTagDtos
     */
    public array $resourceTagDtos = [];

    /**
     * @var ResourceActorDto[] $resourceActorDtos
     */
    public array $resourceActorDtos = [];

    /**
     * @var ResourceCategoryDto[] $resourceCategoryDtos
     */
    public array $resourceCategoryDtos = [];

    /**
     * @var MetaDto[] $metaJson
     */
    public array $metaJson = [];
}
