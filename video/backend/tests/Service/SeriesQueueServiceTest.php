<?php

use App\Dtos\AlbumQueueDto;
use App\Dtos\SeriesQueueDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoQueueDto;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\MediaQueue;
use App\Models\SeriesQueue;
use App\Models\VideoQueue;
use App\Services\SeriesQueueService;
use Tests\TestCase;

class SeriesQueueServiceTest extends TestCase
{
    public function testCreateSeriesQueue(): void
    {
        $resource = $this->createResource();

        $dto = new SeriesQueueDto([
            'name' => 'test series',
            'description' => 'test description',
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoQueueDtos' => [
                new VideoQueueDto([
                    'resourceId' => $resource->id,
                    'resourceVideoUrl' => 'http://www.test1.com',
                ]),
                new VideoQueueDto([
                    'resourceId' => $resource->id,
                    'resourceVideoUrl' => 'http://www.test2.com',
                ]),
            ],
            'albumQueueDtos' => [
                new AlbumQueueDto([
                    'resourceId' => $resource->id,
                    'resourceAlbumUrl' => 'http://www.test3.com',
                ]),
            ],
        ]);

        /**
         * @var SeriesQueueService $seriesQueueService
         */
        $seriesQueueService = app(SeriesQueueService::class);
        $seriesQueue = $seriesQueueService->createSeriesQueue($dto);

        $this->assertEquals(MediaQueue::STATUS_PENDING, $seriesQueue->status);
        $this->assertEquals('', $seriesQueue->thumbnailFile->upload_path);

        $videoQueueQuery = VideoQueue::query()->where('series_queue_id', '=', $seriesQueue->id);
        $this->assertEquals(2, $videoQueueQuery->count());
        $this->assertEquals(VideoQueue::STATUS_PENDING, $videoQueueQuery->first()->status);


        $albumQueueQuery = AlbumQueue::query()->where('series_queue_id', '=', $seriesQueue->id);
        $this->assertEquals(1, $albumQueueQuery->count());
        $this->assertEquals(AlbumQueue::STATUS_PENDING, $albumQueueQuery->first()->status);
    }

    /**
     * @throws Exception
     */
    public function testCreateSeriesFromSeriesQueue()
    {
        /**
         * @var SeriesQueueService $seriesQueueService
         */
        $seriesQueueService = app(SeriesQueueService::class);

        $resource = $this->createResource();

        $dto = new SeriesQueueDto([
            'name' => 'test series',
            'description' => 'test description',
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoQueueDtos' => [
                new VideoQueueDto([
                    'resourceId' => $resource->id,
                    'resourceVideoUrl' => 'http://www.test1.com',
                ]),
            ],
            'albumQueueDtos' => [
                new AlbumQueueDto([
                    'resourceId' => $resource->id,
                    'resourceAlbumUrl' => 'http://www.test3.com',
                ]),
            ],
        ]);

        $seriesQueue = $seriesQueueService->createSeriesQueue($dto);


        //test start series queue

        /**
         * @var AlbumQueue $albumQueue
         */
        $albumQueue = $seriesQueue->albumQueues->first();
        $albumQueue->status = AlbumQueue::STATUS_STARTED;
        $albumQueue->save();

        $seriesQueue->refresh();
        $this->assertEquals(SeriesQueue::STATUS_STARTED, $seriesQueue->status);


        //test complete part of series queue
        $album = $this->createAlbum();
        $albumQueue->album_id = $album->id;
        $albumQueue->status = AlbumQueue::STATUS_COMPLETED;
        $albumQueue->save();

        $seriesQueue->refresh();
        $this->assertEquals(SeriesQueue::STATUS_STARTED, $seriesQueue->status);


        //test complete all series queue
        $video = $this->createVideo();

        /**
         * @var VideoQueue $videoQueue
         */
        $videoQueue = $seriesQueue->videoQueues->first();
        $videoQueue->status = VideoQueue::STATUS_COMPLETED;
        $videoQueue->video_id = $video->id;
        $videoQueue->save();

        $seriesQueue->refresh();
        $this->assertEquals(SeriesQueue::STATUS_COMPLETED, $seriesQueue->status);

        $this->assertEquals($seriesQueue->name, $seriesQueue->series->name);
        $this->assertEquals($seriesQueue->description, $seriesQueue->series->description);
        $this->assertEquals($video->id, $seriesQueue->series->videos->first()->id);
        $this->assertEquals($album->id, $seriesQueue->series->albums->first()->id);
    }
}
