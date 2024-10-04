<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class PlaylistQueueSearchDto extends DataTransferObject
{
    public array $playlistQueueIds;
    public array $statuses;
    public int $resourceId;
}
