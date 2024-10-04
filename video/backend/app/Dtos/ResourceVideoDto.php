<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class ResourceVideoDto extends DataTransferObject
{
    public int $resourceVideoId = 0;
    public int $resourceId;
    public string $name;
    public ?string $description = null;
    public string $resourceVideoUrl;
    public ?int $durationInSeconds = null;
    public FileDto $videoFileDto;
    public ?FileDto $thumbnailFileDto = null;
    public ?FileDto $previewFileDto = null;
    public ?FileDto $downloadFileDto = null;

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
