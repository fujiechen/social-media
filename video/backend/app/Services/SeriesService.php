<?php

namespace App\Services;

use App\Dtos\SeriesDto;
use App\Models\Album;
use App\Models\Media;
use App\Models\Series;
use App\Models\SeriesAlbum;
use App\Models\SeriesVideo;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class SeriesService
{
    private MediaService $mediaService;
    private FileService $fileService;
    private VideoService $videoService;
    private AlbumService $albumService;

    public function __construct(MediaService $mediaService, FileService $fileService, VideoService $videoService, AlbumService $albumService)
    {
        $this->mediaService = $mediaService;
        $this->fileService = $fileService;
        $this->videoService = $videoService;
        $this->albumService = $albumService;
    }

    /**
     * Create for type upload, resource, cloud
     * Update for cloud only
     *
     * @param SeriesDto $dto
     * @return Series
     */
    public function updateOrCreateSeries(SeriesDto $dto): Series
    {
        return DB::transaction(function() use ($dto) {
            $series = Series::updateOrCreate([
                'id' => $dto->seriesId,
            ], [
                'name' => $dto->name,
                'type' => Series::TYPE_CLOUD,
                'description' => $dto->description,
                'thumbnail_file_id' => $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id,
            ]);

            SeriesVideo::query()->where('series_id', '=', $series->id)->delete();

            foreach ($dto->videoDtos as $videoDto) {
                $video = Video::find($videoDto->videoId);
                if ($video == null) {
                    $video = $this->videoService->updateOrCreateVideo($videoDto);
                }
                $videoDto->videoId = $video->id;

                SeriesVideo::create([
                    'series_id' => $series->id,
                    'video_id' => $videoDto->videoId,
                ]);
            }

            foreach ($dto->albumDtos as $albumDto) {
                $album = Album::find($albumDto->albumId);
                if ($album == null) {
                    $album = $this->albumService->updateOrCreateAlbum($albumDto);
                }
                $albumDto->albumId = $album->id;

                SeriesAlbum::create([
                    'series_id' => $series->id,
                    'album_id' => $albumDto->albumId,
                ]);
            }

            return $series;
        });
    }

    public function fetchOtherVideoIdsInSeries(int $videoId): array {
        $videoIds = [];

        /**
         * @var Video $video
         */
        $video = Video::find($videoId);
        foreach ($video->series as $series) {
            $videoIds = SeriesVideo::query()
                ->select('*')
                ->where('series_id', '=', $series->id)
                ->pluck('video_id')
                ->toArray();
        }

        return array_values(array_filter($videoIds, function($item) use ($videoId) {
            return $item != $videoId;
        }));
    }

    public function postDeleted(Series $series): void {
        foreach ($series->videos as $video) {
            $video->delete();
        }

        foreach ($series->albums as $album) {
            $album->delete();
        }

        foreach ($series->medias as $media) {
            $media->delete();
        }
    }

    /**
     * @param int $seriesId
     * @return void
     */
    public function postUpdated(int $seriesId): void {
        /**
         * @var Series $series
         */
        $series = Series::find($seriesId);

        /**
         * @var Media $media
         */
        foreach ($this->mediaService->fetchAllMediasBySeries($seriesId) as $media) {
            if ($series->name != $media->name) {
                $media->name = $series->name;
            }

            if ($series->description != $media->description) {
                $media->description = $series->description;
            }

            Media::withoutEvents(function () use ($media) {
               $media->save();
            });
        }
    }
}
