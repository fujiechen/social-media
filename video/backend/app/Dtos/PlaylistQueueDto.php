<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class PlaylistQueueDto extends DataTransferObject
{
    public int $playlistQueueId;
    public int $resourceId;
    public string $resourcePlaylistUrl;
    public string $status;
}
