<?php

namespace App\Transformers;

use App\Models\PlaylistQueue;
use League\Fractal\TransformerAbstract;

class PlaylistQueueTransformer extends TransformerAbstract
{
    public function transform(?PlaylistQueue $playlistQueue): array
    {
        if (empty($playlistQueue)) {
            return [];
        }

        return [
            'id' => $playlistQueue->id,
            'resource_id' => $playlistQueue->resource_id,
            'resource_playlist_url' => $playlistQueue->resource_playlist_url,
            'status' => $playlistQueue->status,
            'media_queue_id' => $playlistQueue->media_queue_id
        ];
    }

}
