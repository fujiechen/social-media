<?php

namespace App\Dtos;


class MediaSeriesQueueDto extends MediaQueueDto
{
    public string $name;
    public ?string $description = null;
    public ?FileDto $thumbnailFileDto = null;
    /**
     * @var VideoQueueDto[] $videoQueueDtos
     */
    public array $videoQueueDtos = [];
    /**
     * @var AlbumQueueDto[] $albumQueueDtos
     */
    public array $albumQueueDtos = [];
}
