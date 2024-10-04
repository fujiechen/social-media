<?php

namespace App\Transformers;

use App\Models\Tag;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract
{
    public function transform(?Tag $tag): array
    {
        if (empty($tag)) {
            return [];
        }

        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'medias' => [
                'medias_count' => $tag->active_media_videos_count + $tag->active_media_albums_count + $tag->active_media_series_count,
                'series_count' => $tag->active_media_series_count,
                'videos_count' => $tag->active_media_videos_count ,
                'albums_count' => $tag->active_media_albums_count,
            ]
        ];
    }
}
