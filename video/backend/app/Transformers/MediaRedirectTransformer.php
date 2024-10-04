<?php

namespace App\Transformers;

use App\Models\Album;
use App\Models\Media;
use App\Models\Series;
use App\Models\Video;
use League\Fractal\TransformerAbstract;

class MediaRedirectTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private SeriesTransformer $seriesTransformer;
    private VideoTransformer $videoTransformer;
    private AlbumTransformer $albumTransformer;

    public function __construct(
        FileTransformer     $fileTransformer,
        SeriesTransformer   $seriesTransformer,
        VideoTransformer    $videoTransformer,
        AlbumTransformer    $albumTransformer)
    {
        $this->fileTransformer = $fileTransformer;
        $this->seriesTransformer = $seriesTransformer;
        $this->videoTransformer = $videoTransformer;
        $this->albumTransformer = $albumTransformer;
    }

    public function transform(Media $media): array
    {
        $includes = [];
        if ($this->getCurrentScope()) {
            $includes = $this->getCurrentScope()->getManager()->getRequestedIncludes();
        }

        $includeMediaFile = false;
        if (in_array('media_file', $includes)) {
            $includeMediaFile = true;
        }

        $includeDownloadFile = false;
        if (in_array('download_file', $includes)) {
            $includeDownloadFile = true;
        }

        $data = [];

        if ($media->isAlbum()) {
            /**
             * @var Album $album
             */
            $album = $media->mediaable;
            if ($includeDownloadFile) {
                $data['download_file'] = $this->fileTransformer->transform($album->downloadFile);
            }

            if ($includeMediaFile) {
                foreach ($album->images as $image) {
                    $data['images'][] = $this->fileTransformer->transform($image);
                }
            }
        } else if ($media->isVideo()) {
            /**
             * @var Video $video
             */
            $video = $media->mediaable;
            if ($includeMediaFile) {
                $data['video_file'] = $this->fileTransformer->transform($video->videoFile);
            }
            if ($includeDownloadFile) {
                $data['download_file'] = $this->fileTransformer->transform($video->downloadFile);
            }
        }

        //child video in a series media
        /**
         * @var Media $parentMedia
         */
        $parentMedia = $media->parentMedia;
        if (($media->isAlbum() || $media->isVideo()) && !empty($parentMedia) && $parentMedia->status == Media::STATUS_ACTIVE) {
            $seriesMediaIds = $parentMedia->childrenMedias->pluck('id');
            $index = $seriesMediaIds->search($media->id);

            /**
             * @var Media $nextMedia
             */
            $nextMedia = null;
            $nextMediaId = null;
            if ($index !== false && $index < $seriesMediaIds->count() - 1) {
                $nextMediaId = $seriesMediaIds[$index + 1];
                $nextMedia = $parentMedia->childrenMedias->where('id', '=', $nextMediaId)->first();
            }

            /**
             * @var Series $parentSeries
             */
            $parentSeries = $parentMedia->mediaable;
            $data['parent_series'] = $this->seriesTransformer->transform($parentSeries);
            $data['parent_series']['media_id'] = $parentMedia->id;
            $data['parent_series']['media_total_like'] = $parentMedia ? $parentMedia->likes_count : 0;

            if ($nextMediaId && $nextMedia->isVideo()) {
                $data['next_media'] = $this->videoTransformer->transform($nextMedia->mediaable);
            }

            if ($nextMediaId && $nextMedia->isAlbum()) {
                $data['next_media'] = $this->albumTransformer->transform($nextMedia->mediaable);
            }

            $data['next_media']['media_id'] = $nextMediaId;
            $data['next_media']['media_total_like'] = $nextMediaId ? $nextMedia->likes_count : 0;
            $data['next_media']['type'] = $nextMediaId ? $nextMedia->type : null;
        }

        return $data;
    }
}
