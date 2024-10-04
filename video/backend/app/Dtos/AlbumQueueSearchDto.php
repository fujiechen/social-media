<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class AlbumQueueSearchDto extends DataTransferObject
{
    public array $albumQueueIds;
    public array $statuses;
    public int $resourceId;
    public string $resourceAlbumUrl;
}
