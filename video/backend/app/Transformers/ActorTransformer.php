<?php

namespace App\Transformers;

use App\Models\Actor;
use League\Fractal\TransformerAbstract;

class ActorTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer)
    {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(?Actor $actor): array
    {
        if (empty($actor)) {
            return [];
        }

        return [
            'id' => $actor->id,
            'name' => $actor->name,
            'country' => $actor->country,
            'avatar_file' => $actor->avatarFile ? $this->fileTransformer->transform($actor->avatarFile) : null,
            'medias' => [
                'medias_count' => $actor->active_media_videos_count + $actor->active_media_albums_count + $actor->active_media_series_count,
                'series_count' => $actor->active_media_series_count,
                'videos_count' => $actor->active_media_videos_count ,
                'albums_count' => $actor->active_media_albums_count,
            ]
        ];
    }
}
