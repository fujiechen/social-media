<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class SeriesDto extends DataTransferObject
{
    public int $seriesId = 0;
    public string $name;
    public string $description;
    public FileDto $thumbnailFileDto;

    /**
     * @var VideoDto[] $videoDtos
     */
    public array $videoDtos = [];

    /**
     * @var AlbumDto[] $albumDtos
     */
    public array $albumDtos = [];
}
