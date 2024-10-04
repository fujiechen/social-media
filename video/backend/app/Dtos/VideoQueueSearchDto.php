<?php
namespace App\Dtos;

use App\Utils\DataTransferObject;

class VideoQueueSearchDto extends DataTransferObject
{
    public array $videoQueueIds;
    public array $statuses;
    public int $resourceId;
    public string $resourceVideoUrl;
}
