<?php

namespace App\Services;

use App\Dtos\AlbumDto;
use App\Dtos\BucketFileDto;
use App\Dtos\SeriesDto;
use App\Dtos\SeriesQueueDto;
use App\Dtos\VideoDto;
use App\Models\Album;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\Series;
use App\Models\SeriesQueue;
use App\Models\Video;
use App\Models\VideoQueue;
use Illuminate\Support\Facades\DB;

class SeriesQueueService
{
    private SeriesService $seriesService;
    private FileService $fileService;
    private VideoQueueService $videoQueueService;
    private AlbumQueueService $albumQueueService;

    public function __construct(SeriesService $seriesService, FileService $fileService,
                                VideoQueueService $videoQueueService, AlbumQueueService $albumQueueService) {
        $this->seriesService = $seriesService;
        $this->fileService = $fileService;
        $this->videoQueueService = $videoQueueService;
        $this->albumQueueService = $albumQueueService;
    }

    public function createSeriesQueue(SeriesQueueDto $dto): SeriesQueue {

        return DB::transaction(function () use ($dto) {
            $seriesQueue = SeriesQueue::create([
                'thumbnail_file_id' => $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id,
                'name' => $dto->name,
                'description' => $dto->description,
                'media_queue_id' => $dto->mediaQueueId,
            ]);

            foreach ($dto->videoQueueDtos as $videoQueueDto) {
                $videoQueueDto->seriesQueueId = $seriesQueue->id;
                $this->videoQueueService->updateOrCreateVideoQueue($videoQueueDto);
            }

            foreach ($dto->albumQueueDtos as $albumQueueDto) {
                $albumQueueDto->seriesQueueId = $seriesQueue->id;
                $this->albumQueueService->updateOrCreateAlbumQueue($albumQueueDto);
            }

            return $seriesQueue;
        });
    }

    public function updateSeriesQueueStatusOfAlbumQueue(int $albumQueueId): bool {
        return DB::transaction(function() use ($albumQueueId) {
            /**
             * @var AlbumQueue $albumQueue
             */
            $albumQueue = AlbumQueue::find($albumQueueId);

            if (!$albumQueue->series_queue_id) {
                return true;
            }

            /**
             * @var SeriesQueue $seriesQueue
             */
            $seriesQueue = $albumQueue->seriesQueue;

            if (!$seriesQueue) {
                return true;
            }

            if ($albumQueue->status == AlbumQueue::STATUS_COMPLETED) {
                $this->completeSeriesQueue($seriesQueue);
            } else if ($albumQueue->status == AlbumQueue::STATUS_STARTED) {
                $seriesQueue->status = SeriesQueue::STATUS_STARTED;
            } else if ($albumQueue->status == AlbumQueue::STATUS_ERROR) {
                $seriesQueue->status = SeriesQueue::STATUS_ERROR;
            }

            $seriesQueue->save();

            return true;
        });
    }

    public function updateSeriesQueueStatusOfVideoQueue(int $videoQueueId): bool {
        return DB::transaction(function() use ($videoQueueId) {
            /**
             * @var VideoQueue $videoQueue
             */
            $videoQueue = VideoQueue::find($videoQueueId);

            if (!$videoQueue->series_queue_id) {
                return true;
            }

            /**
             * @var SeriesQueue $seriesQueue
             */
            $seriesQueue = $videoQueue->seriesQueue;

            if (!$seriesQueue) {
                return true;
            }

            if ($videoQueue->status == VideoQueue::STATUS_COMPLETED) {
                $this->completeSeriesQueue($seriesQueue);
            } else if ($videoQueue->status == VideoQueue::STATUS_STARTED) {
                $seriesQueue->status = SeriesQueue::STATUS_STARTED;
                $seriesQueue->save();
            } else if ($videoQueue->status == VideoQueue::STATUS_ERROR) {
                $seriesQueue->status = SeriesQueue::STATUS_ERROR;
                $seriesQueue->save();
            }

            return true;
        });
    }

    public function completeSeriesQueue(SeriesQueue $seriesQueue): void
    {
        //all video queues and album queue need to complete then create series from series queue
        $seriesQueueCompleted = true;
        foreach ($seriesQueue->videoQueues as $vq) {
            if ($vq->status != VideoQueue::STATUS_COMPLETED) {
                $seriesQueueCompleted = false;
            }
        }

        foreach ($seriesQueue->albumQueues as $aq) {
            if ($aq->status != VideoQueue::STATUS_COMPLETED) {
                $seriesQueueCompleted = false;
            }
        }

        if ($seriesQueueCompleted) {
            $series = $this->createSeriesFromSeriesQueue($seriesQueue);
            $seriesQueue->status = SeriesQueue::STATUS_COMPLETED;
            $seriesQueue->series_id = $series->id;
            $seriesQueue->save();
        }
    }


    public function createSeriesFromSeriesQueue(SeriesQueue $seriesQueue): Series {
        return DB::transaction(function () use ($seriesQueue) {
            $videoDtos = [];
            foreach ($seriesQueue->videoQueues as $videoQueue) {
                if ($videoQueue->video_id) {
                    $videoDtos[] = new VideoDto([
                        'videoId' => $videoQueue->video_id,
                        'type' => Video::TYPE_CLOUD
                    ]);
                }
            }

            $albumDtos = [];
            foreach ($seriesQueue->albumQueues as $albumQueue) {
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
                'name' => $seriesQueue->name,
                'description' => $seriesQueue->description,
                'thumbnailFileDto' => new BucketFileDto([
                    'fileId' => $seriesQueue->thumbnailFile->id,
                    'bucketType' => File::TYPE_PUBLIC_BUCKET,
                    'bucketName' => $seriesQueue->thumbnailFile->bucket_name,
                    'bucketFileName' => $seriesQueue->thumbnailFile->bucket_file_name,
                    'bucketFilePath' => $seriesQueue->thumbnailFile->bucket_file_path,
                ]),
                'videoDtos' => $videoDtos,
                'albumDtos' => $albumDtos,
            ]);

            return $this->seriesService->updateOrCreateSeries($seriesDto);
        });
    }

}
