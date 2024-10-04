<?php

namespace App\Transformers;

use App\Models\AlbumQueue;
use League\Fractal\TransformerAbstract;

class AlbumQueueTransformer extends TransformerAbstract
{
    public function transform(?AlbumQueue $albumQueue): array
    {
        if (empty($albumQueue)) {
            return [];
        }

        return [
            'id' => $albumQueue->id,
            'resource_album_url' => $albumQueue->resource_album_url,
            'status' => $albumQueue->status,
        ];
    }

}
