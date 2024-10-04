<?php

namespace App\Transformers;

use App\Models\Video;
use League\Fractal\TransformerAbstract;

class VideoTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer)
    {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(Video $video): array
    {
        $data = [
            'id' => $video->id,
            'name' => $video->name,
            'description' => $video->description,
            'thumbnail_file' => $video->thumbnailFile ? $this->fileTransformer->transform($video->thumbnailFile) : null,
            'preview_file' => $video->previewFile ? $this->fileTransformer->transform($video->previewFile) : null,
            'duration_in_seconds' => $video->duration_in_seconds,
            'created_at' => $video->created_at,
        ];

        return $data;
    }
}
