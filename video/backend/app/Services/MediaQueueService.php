<?php

namespace App\Services;

use App\Dtos\AlbumDto;
use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\MediaAlbumQueueDto;
use App\Dtos\MediaDto;
use App\Dtos\MediaPlaylistQueueDto;
use App\Dtos\MediaSeriesQueueDto;
use App\Dtos\MediaVideoQueueDto;
use App\Dtos\SeriesDto;
use App\Dtos\SeriesQueueDto;
use App\Dtos\VideoDto;
use App\Models\Album;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\Media;
use App\Models\MediaQueue;
use App\Models\PlaylistQueue;
use App\Models\Series;
use App\Models\Video;
use App\Models\VideoQueue;
use Illuminate\Support\Facades\DB;

class MediaQueueService
{
    private MediaService $mediaService;
    private VideoQueueService $videoQueueService;
    private SeriesService $seriesService;
    private FileService $fileService;
    private AlbumQueueService $albumQueueService;
    private PlaylistQueueService $playlistQueueService;
    private SeriesQueueService $seriesQueueService;

    public function __construct(MediaService $mediaService, VideoQueueService $videoQueueService,
                                SeriesService $seriesService, FileService $fileService,
                                AlbumQueueService $albumQueueService, PlaylistQueueService $playlistQueueService,
                                SeriesQueueService $seriesQueueService) {
        $this->mediaService = $mediaService;
        $this->videoQueueService = $videoQueueService;
        $this->seriesService = $seriesService;
        $this->fileService = $fileService;
        $this->albumQueueService = $albumQueueService;
        $this->playlistQueueService = $playlistQueueService;
        $this->seriesQueueService = $seriesQueueService;
    }

    public function createMediaPlaylistQueue(MediaPlaylistQueueDto $dto): MediaQueue {
        return DB::transaction(function () use ($dto) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::create([
                'user_id' => $dto->userId,
                'media_type' => $dto->mediaType,
                'role_ids' => $dto->mediaRoleIds,
            ]);

            $playlistQueue = $this->playlistQueueService->updateOrCreatePlaylistQueue($dto->playlistQueueDto);
            $mediaQueueId = $mediaQueue->id;
            PlaylistQueue::withoutEvents(function () use ($playlistQueue, $mediaQueueId) {
                $playlistQueue->media_queue_id = $mediaQueueId;
                $playlistQueue->save();
            });

            return $mediaQueue;
        });
    }

    public function createMediaAlbumQueue(MediaAlbumQueueDto $dto): MediaQueue {
        return DB::transaction(function () use ($dto) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::create([
                'user_id' => $dto->userId,
                'media_type' => $dto->mediaType,
                'role_ids' => $dto->mediaRoleIds,
            ]);

            $dto->albumQueueDto->mediaQueueId = $mediaQueue->id;
            $this->albumQueueService->updateOrCreateAlbumQueue($dto->albumQueueDto);
            $this->albumQueueService->updateOrCreateAlbumQueue($dto->albumQueueDto);

            return $mediaQueue;
        });
    }

    public function createMediaVideoQueue(MediaVideoQueueDto $dto): MediaQueue {
        return DB::transaction(function () use ($dto) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::create([
                'user_id' => $dto->userId,
                'media_type' => $dto->mediaType,
                'role_ids' => $dto->mediaRoleIds,
            ]);

            $dto->videoQueueDto->mediaQueueId = $mediaQueue->id;
            $this->videoQueueService->updateOrCreateVideoQueue($dto->videoQueueDto);

            return $mediaQueue;
        });
    }

    public function createMediaSeriesQueue(MediaSeriesQueueDto $dto): MediaQueue {

        return DB::transaction(function () use ($dto) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::create([
                'user_id' => $dto->userId,
                'media_type' => $dto->mediaType,
                'thumbnail_file_id' => $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id,
                'name' => $dto->name,
                'description' => $dto->description,
                'role_ids' => $dto->mediaRoleIds,
            ]);

            $videoQueueDtos = [];
            foreach ($dto->videoQueueDtos as $videoQueueDto) {
                $videoQueueDto->mediaQueueId = $mediaQueue->id;
                $videoQueueDtos[] = $videoQueueDto;
            }

            $albumQueueDtos = [];
            foreach ($dto->albumQueueDtos as $albumQueueDto) {
                $albumQueueDto->mediaQueueId = $mediaQueue->id;
                $albumQueueDtos[] = $albumQueueDto;
            }

            $this->seriesQueueService->createSeriesQueue(new SeriesQueueDto([
                'name' => $dto->name,
                'description' => $dto->description,
                'thumbnailFileDto' => new BucketFileDto([
                    'fileId' => $mediaQueue->thumbnail_file_id,
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                ]),
                'videoQueueDtos' => $videoQueueDtos,
                'albumQueueDtos' => $albumQueueDtos,
                'mediaQueueId' => $mediaQueue->id,
            ]));

            return $mediaQueue;
        });
    }

    public function updateMediaQueueStatusOfAlbumQueue(int $albumQueueId): bool {
        return DB::transaction(function() use ($albumQueueId) {
            /**
             * @var AlbumQueue $albumQueue
             */
            $albumQueue = AlbumQueue::find($albumQueueId);

            if (!$albumQueue->media_queue_id) {
                return true;
            }

            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = $albumQueue->mediaQueue;

            if (!$mediaQueue) {
                return true;
            }

            if ($albumQueue->status == AlbumQueue::STATUS_COMPLETED) {

                if ($mediaQueue->media_type == MediaQueue::TYPE_ALBUM
                    || $mediaQueue->media_type == MediaQueue::TYPE_PLAYLIST) {
                    $media = $this->createMediaFromAlbumQueue($mediaQueue->id, $albumQueueId);
                    $mediaQueue->status = MediaQueue::STATUS_COMPLETED;
                    $mediaQueue->media_id = $media->id;
                } else {
                    $this->createSeriesFromMediaQueue($mediaQueue);
                }
            } else if ($albumQueue->status == AlbumQueue::STATUS_STARTED) {
                $mediaQueue->status = MediaQueue::STATUS_STARTED;
            } else if ($albumQueue->status == AlbumQueue::STATUS_ERROR) {
                $mediaQueue->status = MediaQueue::STATUS_ERROR;
            }

            $mediaQueue->save();

            return true;
        });
    }

    public function updateMediaQueueStatusOfVideoQueue(int $videoQueueId): bool {
        return DB::transaction(function() use ($videoQueueId) {
            /**
             * @var VideoQueue $videoQueue
             */
            $videoQueue = VideoQueue::find($videoQueueId);

            if (!$videoQueue->media_queue_id) {
                return true;
            }

            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = $videoQueue->mediaQueue;

            if (!$mediaQueue) {
                return true;
            }

            if ($videoQueue->status == VideoQueue::STATUS_COMPLETED) {

                if ($mediaQueue->media_type == MediaQueue::TYPE_VIDEO
                    || $mediaQueue->media_type == MediaQueue::TYPE_PLAYLIST) {

                    $media = $this->createMediaFromVideoQueue($mediaQueue->id, $videoQueueId);
                    $mediaQueue->status = MediaQueue::STATUS_COMPLETED;
                    $mediaQueue->media_id = $media->id;
                } else {
                    $this->createSeriesFromMediaQueue($mediaQueue);
                }
            } else if ($videoQueue->status == VideoQueue::STATUS_STARTED) {
                $mediaQueue->status = MediaQueue::STATUS_STARTED;
            } else if ($videoQueue->status == VideoQueue::STATUS_ERROR) {
                $mediaQueue->status = MediaQueue::STATUS_ERROR;
            }

            $mediaQueue->save();

            return true;
        });
    }

    public function createMediaFromAlbumQueue(int $mediaQueueId, int $albumQueueId): ?Media {
        return DB::transaction(function () use ($mediaQueueId, $albumQueueId) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::find($mediaQueueId);

            /**
             * @var AlbumQueue $albumQueue
             */
            $albumQueue = AlbumQueue::find($albumQueueId);

            /**
             * @var Album $album
             */
            $album = Album::find($albumQueue->album_id);

            if (!$album) {
                return null;
            }

            $mediaDto = new MediaDto([
                'mediaId' => 0,
                'userId' => $mediaQueue->user_id,
                'mediaableType' => Media::toMediaableType(Media::TYPE_ALBUM),
                'albumId' => $album->id,
                'mediaRoleIds' => explode(',', $mediaQueue->role_ids),
                'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
                'isActive' => false,
            ]);

            return $this->mediaService->updateOrCreateMedia($mediaDto);
        });
    }

    public function createMediaFromVideoQueue(int $mediaQueueId, int $videoQueueId): ?Media {
        return DB::transaction(function () use ($mediaQueueId, $videoQueueId) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::find($mediaQueueId);

            /**
             * @var VideoQueue $videoQueue
             */
            $videoQueue = VideoQueue::find($videoQueueId);

            /**
             * @var Video $video
             */
            $video = Video::find($videoQueue->video_id);

            if (!$video) {
                return null;
            }

            $mediaDto = new MediaDto([
                'mediaId' => 0,
                'userId' => $mediaQueue->user_id,
                'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
                'videoId' => $video->id,
                'mediaRoleIds' => explode(',', $mediaQueue->role_ids),
                'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
                'isActive' => false,
            ]);

            return $this->mediaService->updateOrCreateMedia($mediaDto);
        });
    }

    public function createMediaFromSeriesQueue(int $mediaQueueId): Media {
        return DB::transaction(function () use ($mediaQueueId) {
            /**
             * @var MediaQueue $mediaQueue
             */
            $mediaQueue = MediaQueue::find($mediaQueueId);

            $videoDtos = [];
            foreach ($mediaQueue->videoQueues as $videoQueue) {
                if ($videoQueue->video_id) {
                    $videoDtos[] = new VideoDto([
                        'videoId' => $videoQueue->video_id,
                        'type' => Video::TYPE_CLOUD
                    ]);
                }
            }

            $albumDtos = [];
            foreach ($mediaQueue->albumQueues as $albumQueue) {
                if ($albumQueue->album_id) {
                    $albumDtos[] = new AlbumDto([
                        'albumId' => $albumQueue->album_id,
                        'type' => Album::TYPE_CLOUD
                    ]);
                }
            }

            $seriesDto = new SeriesDto([
                'seriesId' => 0,
                'type' => Series::TYPE_CLOUD,
                'name' => $mediaQueue->name,
                'description' => $mediaQueue->description,
                'thumbnailFileDto' => new BucketFileDto([
                    'fileId' => $mediaQueue->thumbnailFile->id,
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                    'bucketName' => $mediaQueue->thumbnailFile->bucket_name,
                    'bucketFileName' => $mediaQueue->thumbnailFile->bucket_file_name,
                    'bucketFilePath' => $mediaQueue->thumbnailFile->bucket_file_path,
                ]),
                'videoDtos' => $videoDtos,
                'albumDtos' => $albumDtos,
            ]);

            $series = $this->seriesService->updateOrCreateSeries($seriesDto);
            $mediaDto = new MediaDto([
                'mediaId' => 0,
                'userId' => $mediaQueue->user_id,
                'mediaableType' => Media::toMediaableType($mediaQueue->media_type),
                'seriesId' => $series->id,
                'mediaRoleIds' => explode(',', $mediaQueue->role_ids),
                'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
                'isActive' => false,
            ]);
            return $this->mediaService->updateOrCreateMedia($mediaDto);
        });
    }

    /**
     * @param MediaQueue $mediaQueue
     * @return void
     */
    public function createSeriesFromMediaQueue(MediaQueue $mediaQueue): void
    {
        if ($mediaQueue->media_type == MediaQueue::TYPE_SERIES) {
            //all video queues and album queue need to complete then create media from series

            $seriesQueueCompleted = true;
            foreach ($mediaQueue->videoQueues as $vq) {
                if ($vq->status != VideoQueue::STATUS_COMPLETED) {
                    $seriesQueueCompleted = false;
                }
            }

            foreach ($mediaQueue->albumQueues as $aq) {
                if ($aq->status != VideoQueue::STATUS_COMPLETED) {
                    $seriesQueueCompleted = false;
                }
            }

            if ($seriesQueueCompleted) {
                $media = $this->createMediaFromSeriesQueue($mediaQueue->id);
                $mediaQueue->status = MediaQueue::STATUS_COMPLETED;
                $mediaQueue->media_id = $media->id;
                $mediaQueue->save();
            }
        }
    }

}
