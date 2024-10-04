<?php

namespace App\Transformers;

use App\Models\Album;
use League\Fractal\TransformerAbstract;

class AlbumTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer)
    {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(?Album $album): array
    {
        if (empty($album)) {
            return [];
        }

        return [
            'id' => $album->id,
            'name' => $album->name,
            'description' => $album->description,
            'thumbnail_file' => $album->thumbnailFile ? $this->fileTransformer->transform($album->thumbnailFile) : null,
            'created_at' => $album->created_at
        ];
    }


}
