<?php

namespace App\Transformers;

use App\Models\Category;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer) {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(?Category $category): array
    {
        if (empty($category)) {
            return [];
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'avatar_file' => $category->avatarFile ? $this->fileTransformer->transform($category->avatarFile) : null,
            'medias' => [
                'medias_count' => $category->active_media_videos_count + $category->active_media_albums_count + $category->active_media_series_count,
                'series_count' => $category->active_media_series_count,
                'videos_count' => $category->active_media_videos_count ,
                'albums_count' => $category->active_media_albums_count,
            ]
        ];
    }
}
