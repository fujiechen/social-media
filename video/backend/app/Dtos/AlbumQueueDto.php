<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class AlbumQueueDto extends DataTransferObject
{
    public ?int $albumQueueId = null;
    public ?int $playlistQueueId = null;
    public ?int $seriesQueueId = null;
    public ?int $mediaQueueId = null;
    public int $resourceId;
    public string $resourceAlbumUrl;
    public string $status;
    public ?ResourceAlbumDto $resourceAlbumDto = null;
}
