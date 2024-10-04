<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class VideoQueueDto extends DataTransferObject
{
    public ?int $videoQueueId = null;
    public ?int $playlistQueueId = null;
    public ?int $seriesQueueId = null;
    public ?int $mediaQueueId = null;
    public int $resourceId;
    public string $resourceVideoUrl;
    public string $status;
    public ?ResourceVideoDto $resourceVideoDto = null;
    public ?array $prefillJson = null;
}
