<?php

namespace App\Transformers;

use App\Models\Series;
use League\Fractal\TransformerAbstract;

class SeriesTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer)
    {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(Series $series): array
    {
        $totalChildrenVideos = $series->totalChildrenVideos();
        $totalChildAlbums = $series->totalChildrenVideos();

        return [
            'id' => $series->id,
            'name' => $series->name,
            'description' => $series->description,
            'thumbnail_file' => $series->thumbnailFile ? $this->fileTransformer->transform($series->thumbnailFile) : null,
            'total_children_videos' => $totalChildrenVideos,
            'total_children_albums' => $totalChildAlbums,
            'total_medias' => $totalChildrenVideos + $totalChildAlbums,
            'created_at' => $series->created_at,
        ];
    }


}
