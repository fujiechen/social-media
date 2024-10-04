<?php

namespace App\Transformers;

use App\Models\VideoQueue;
use League\Fractal\TransformerAbstract;

class VideoQueueTransformer extends TransformerAbstract
{
    public function transform(?VideoQueue $videoQueue): array
    {
        if (empty($videoQueue)) {
            return [];
        }

        return [
            'id' => $videoQueue->id,
            'resource_video_url' => $videoQueue->resource_video_url,
            'status' => $videoQueue->status,
            'resource_id' => $videoQueue->resource_id,
            'playlist_queue_id' => $videoQueue->playlist_queue_id,
        ];
    }

}
