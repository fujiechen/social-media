<?php

namespace App\Transformers;

use App\Models\Media;
use League\Fractal\TransformerAbstract;

class MediaMetaTransformer extends TransformerAbstract
{
    public function transform(Media $media): array
    {
        return [
            'meta' => [
                'count' => [
                    'comments' => $media->comments_count,
                    'favorites' => $media->favorites_count,
                    'likes' => $media->likes_count,
                    'children_medias' => $media->children_count,
                ],
                'user' => [
                    'subscribe' => (bool)$media->is_user_subscribe,
                    'like' => (bool)$media->is_user_like,
                    'favorite' => (bool)$media->is_user_favorite,
                ]
            ]
        ];
    }
}
