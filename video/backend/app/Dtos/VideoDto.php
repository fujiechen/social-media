<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class VideoDto extends DataTransferObject
{
    public int $videoId = 0;
    public string $type;
    public string $name = '';
    public string $description = '';
    public ?int $durationInSeconds = null;

    public FileDto $thumbnailFileDto;
    public FileDto $videoFileDto;
    public ?FileDto $previewFileDto = null;
    public ?FileDto $downloadFileDto = null;

    public int $resourceVideoId;
    public array $tagIds = [];
    public array $categoryIds = [];
    public array $actorIds = [];

    /**
     * @var MetaDto[] $metaJson
     */
    public array $metaJson = [];
}
