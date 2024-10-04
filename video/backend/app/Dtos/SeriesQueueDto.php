<?php

namespace App\Dtos;


use App\Utils\DataTransferObject;

class SeriesQueueDto extends DataTransferObject
{
    public string $name;
    public ?string $description = null;
    public ?FileDto $thumbnailFileDto = null;
    public ?int $mediaQueueId = null;
    /**
     * @var VideoQueueDto[] $videoQueueDtos
     */
    public array $videoQueueDtos = [];
    /**
     * @var AlbumQueueDto[] $albumQueueDtos
     */
    public array $albumQueueDtos = [];
}
