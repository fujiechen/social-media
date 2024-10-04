<?php

use App\Dtos\AlbumQueueDto;
use App\Dtos\MediaAlbumQueueDto;
use App\Dtos\MediaPlaylistQueueDto;
use App\Dtos\MediaSeriesQueueDto;
use App\Dtos\MediaVideoQueueDto;
use App\Dtos\PlaylistQueueDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoQueueDto;
use App\Models\Album;
use App\Models\AlbumQueue;
use App\Models\File;
use App\Models\Media;
use App\Models\MediaQueue;
use App\Models\PlaylistQueue;
use App\Models\Role;
use App\Models\Video;
use App\Models\VideoQueue;
use App\Services\MediaQueueService;
use App\Services\MediaService;
use Tests\TestCase;

class MediaQueueServiceTest extends TestCase
{
    public function testCreateMediaPlaylistQueue(): void
    {
        $resource = $this->createResource();

        $dto = new MediaPlaylistQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => MediaQueue::TYPE_PLAYLIST,
            'playlistQueueDto' => new PlaylistQueueDto([
                'resourceId' => $resource->id,
                'resourcePlaylistUrl' => 'http://www.test.com',
            ])
        ]);

        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);
        $mediaQueue = $mediaQueueService->createMediaPlaylistQueue($dto);
        $this->assertEquals(Role::ROLE_VISITOR_ID, $mediaQueue->role_ids);
        $this->assertEquals(MediaQueue::STATUS_PENDING, $mediaQueue->status);
        $this->assertEquals(MediaQueue::TYPE_PLAYLIST, $mediaQueue->media_type);

        $playlistQueueQuery = PlaylistQueue::query()->where('media_queue_id', '=', $mediaQueue->id);

        $this->assertEquals(1, $playlistQueueQuery->count());
        $this->assertEquals(PlaylistQueue::STATUS_PENDING, $playlistQueueQuery->first()->status);
    }

    public function testCreateMediaAlbumQueue(): void
    {
        $resource = $this->createResource();

        $dto = new MediaAlbumQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_ALBUM,
            'albumQueueDto' => new AlbumQueueDto([
                'resourceId' => $resource->id,
                'resourceAlbumUrl' => 'http://www.test.com',
            ])
        ]);

        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);
        $mediaQueue = $mediaQueueService->createMediaAlbumQueue($dto);
        $this->assertEquals(Role::ROLE_VISITOR_ID, $mediaQueue->role_ids);
        $this->assertEquals(MediaQueue::STATUS_PENDING, $mediaQueue->status);
        $this->assertEquals(MediaQueue::TYPE_ALBUM, $mediaQueue->media_type);

        $albumQueueQuery = AlbumQueue::query()->where('media_queue_id', '=', $mediaQueue->id);

        $this->assertEquals(1, $albumQueueQuery->count());
        $this->assertEquals($mediaQueue->id, $albumQueueQuery->first()->media_queue_id);
        $this->assertEquals(AlbumQueue::STATUS_PENDING, $albumQueueQuery->first()->status);
    }

    public function testCreateMediaVideoQueue(): void
    {
        $resource = $this->createResource();

        $dto = new MediaVideoQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_VIDEO,
            'videoQueueDto' => new VideoQueueDto([
                'resourceId' => $resource->id,
                'resourceVideoUrl' => 'http://www.test.com',
            ])
        ]);

        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);
        $mediaQueue = $mediaQueueService->createMediaVideoQueue($dto);
        $this->assertEquals(Role::ROLE_VISITOR_ID, $mediaQueue->role_ids);
        $this->assertEquals(MediaQueue::STATUS_PENDING, $mediaQueue->status);
        $this->assertEquals(MediaQueue::TYPE_VIDEO, $mediaQueue->media_type);

        $videoQueueQuery = VideoQueue::query()->where('media_queue_id', '=', $mediaQueue->id);

        $this->assertEquals(1, $videoQueueQuery->count());
        $this->assertEquals($mediaQueue->id, $videoQueueQuery->first()->media_queue_id);
        $this->assertEquals(VideoQueue::STATUS_PENDING, $videoQueueQuery->first()->status);
    }


    public function testCreateMediaSeriesQueue(): void
    {
        $resource = $this->createResource();

        $dto = new MediaSeriesQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_SERIES,
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
                new VideoQueueDto([
                    'resourceId' => $resource->id,
                    'resourceVideoUrl' => 'http://www.test3.com',
                ]),
            ]
        ]);

        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);
        $mediaQueue = $mediaQueueService->createMediaSeriesQueue($dto);
        $this->assertEquals(Role::ROLE_VISITOR_ID, $mediaQueue->role_ids);
        $this->assertEquals(MediaQueue::STATUS_PENDING, $mediaQueue->status);
        $this->assertEquals(MediaQueue::TYPE_SERIES, $mediaQueue->media_type);

        $this->assertEquals('', $mediaQueue->thumbnailFile->upload_path);

        $videoQueueQuery = VideoQueue::query()->where('media_queue_id', '=', $mediaQueue->id);

        $this->assertEquals(3, $videoQueueQuery->count());
        $this->assertEquals(VideoQueue::STATUS_PENDING, $videoQueueQuery->first()->status);
    }

    /**
     * @throws Exception
     */
    public function testUpdateMediaQueueStatusOfAlbumQueue()
    {
        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);

        $resource = $this->createResource();

        $dto = new MediaAlbumQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_ALBUM,
            'albumQueueDto' => new AlbumQueueDto([
                'resourceId' => $resource->id,
                'resourceAlbumUrl' => 'http://www.test.com',
            ])
        ]);

        $mediaQueue = $mediaQueueService->createMediaAlbumQueue($dto);

        /**
         * @var AlbumQueue $albumQueue
         */
        $albumQueue = $mediaQueue->albumQueues->first();

        $albumQueue->status = AlbumQueue::STATUS_STARTED;
        $albumQueue->save();

        //updateMediaQueueStatusOfAlbumQueue is triggered by AlbumQueueUpdatedEventHandler
        $mediaQueue->refresh();
        $this->assertEquals(MediaQueue::STATUS_STARTED, $mediaQueue->status);

        $album = $this->createAlbum();
        $albumQueue->status = AlbumQueue::STATUS_COMPLETED;
        $albumQueue->album_id = $album->id;
        $albumQueue->save();

        $albumQueue->refresh();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $mediaAlbum = $mediaService->fetchMediaableModel(Album::class, $album->id);
        $this->assertNotNull($mediaAlbum);

        $mediaQueue->refresh();

        $this->assertNotNull($mediaQueue->media_id);
        $this->assertEquals(Media::TYPE_ALBUM, $mediaQueue->media->type);
    }


    /**
     * @throws Exception
     */
    public function testUpdateMediaQueueStatusOfVideoQueue()
    {
        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);

        $resource = $this->createResource();

        $dto = new MediaVideoQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_VIDEO,
            'videoQueueDto' => new VideoQueueDto([
                'resourceId' => $resource->id,
                'resourceVideoUrl' => 'http://www.test.com',
            ])
        ]);

        $mediaQueue = $mediaQueueService->createMediaVideoQueue($dto);

        /**
         * @var VideoQueue $videoQueue
         */
        $videoQueue = $mediaQueue->videoQueues->first();

        $videoQueue->status = VideoQueue::STATUS_STARTED;
        $videoQueue->save();

        //updateMediaQueueStatusOfVideoQueue is triggered by VideoQueueUpdatedEventHandler
        $mediaQueue->refresh();
        $this->assertEquals(MediaQueue::STATUS_STARTED, $mediaQueue->status);

        $video = $this->createVideo();
        $videoQueue->status = VideoQueue::STATUS_COMPLETED;
        $videoQueue->video_id = $video->id;
        $videoQueue->save();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $mediaVideo = $mediaService->fetchMediaableModel(Video::class, $video->id);
        $this->assertNotNull($mediaVideo);

        $mediaQueue->refresh();

        $this->assertNotNull($mediaQueue->media_id);
        $this->assertEquals(Media::TYPE_VIDEO, $mediaQueue->media->type);
    }


    /**
     * @throws Exception
     */
    public function testUpdateMediaQueueStatusOfSeriesQueueOfVideoQueueOnly()
    {
        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);

        $resource = $this->createResource();

        $dto = new MediaSeriesQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_SERIES,
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
        ]);

        $mediaQueue = $mediaQueueService->createMediaSeriesQueue($dto);

        /**
         * @var VideoQueue $videoQueue1
         */
        $videoQueue1 = $mediaQueue->videoQueues->first();

        $videoQueue1->status = VideoQueue::STATUS_STARTED;
        $videoQueue1->save();

        //updateMediaQueueStatusOfVideoQueue is triggered by VideoQueueUpdatedEventHandler
        $mediaQueue->refresh();

        $this->assertEquals(MediaQueue::STATUS_STARTED, $mediaQueue->status);
        $this->assertNull($mediaQueue->media_id);

        $video1 = $this->createVideo();
        $videoQueue1->status = VideoQueue::STATUS_COMPLETED;
        $videoQueue1->video_id = $video1->id;
        $videoQueue1->save();
        $videoQueue1->refresh();

        $this->assertNull($mediaQueue->media_id);


        /**
         * @var VideoQueue $videoQueue2
         */
        $videoQueue2 = $mediaQueue->videoQueues->sortByDesc('id')->first();

        $video2 = $this->createVideo();
        $videoQueue2->status = VideoQueue::STATUS_COMPLETED;
        $videoQueue2->video_id = $video2->id;
        $videoQueue2->save();
        $videoQueue2->refresh();

        $mediaQueue->refresh();

        $this->assertNotNull($mediaQueue->media_id);
        $this->assertEquals(Media::TYPE_SERIES, $mediaQueue->media->type);
    }


    public function testUpdateMediaQueueStatusOfSeriesQueueOfAlbumQueueOnly()
    {
        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);

        $resource = $this->createResource();

        $dto = new MediaSeriesQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_SERIES,
            'name' => 'test series',
            'description' => 'test description',
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'albumQueueDtos' => [
                new AlbumQueueDto([
                    'resourceId' => $resource->id,
                    'resourceAlbumUrl' => 'http://www.test1.com',
                ]),
                new AlbumQueueDto([
                    'resourceId' => $resource->id,
                    'resourceAlbumUrl' => 'http://www.test2.com',
                ]),
            ],
        ]);

        $mediaQueue = $mediaQueueService->createMediaSeriesQueue($dto);

        /**
         * @var AlbumQueue $albumQueue1
         */
        $albumQueue1 = $mediaQueue->albumQueues->first();

        $albumQueue1->status = AlbumQueue::STATUS_STARTED;
        $albumQueue1->save();

        //updateMediaQueueStatusOfVideoQueue is triggered by VideoQueueUpdatedEventHandler
        $mediaQueue->refresh();

        $this->assertEquals(MediaQueue::STATUS_STARTED, $mediaQueue->status);
        $this->assertNull($mediaQueue->media_id);

        $album1 = $this->createAlbum();
        $albumQueue1->status = AlbumQueue::STATUS_COMPLETED;
        $albumQueue1->album_id = $album1->id;
        $albumQueue1->save();
        $albumQueue1->refresh();

        $this->assertNull($mediaQueue->media_id);


        /**
         * @var AlbumQueue $albumQueue2
         */
        $albumQueue2 = $mediaQueue->albumQueues->sortByDesc('id')->first();

        $album2 = $this->createAlbum();
        $albumQueue2->status = AlbumQueue::STATUS_COMPLETED;
        $albumQueue2->album_id = $album2->id;
        $albumQueue2->save();
        $albumQueue2->refresh();

        $mediaQueue->refresh();

        $this->assertNotNull($mediaQueue->media_id);
        $this->assertEquals(Media::TYPE_SERIES, $mediaQueue->media->type);
    }


    public function testUpdateMediaQueueStatusOfSeriesQueueOfMix()
    {
        /**
         * @var MediaQueueService $mediaQueueService
         */
        $mediaQueueService = app(MediaQueueService::class);

        $resource = $this->createResource();

        $dto = new MediaSeriesQueueDto([
            'userId' => $this->adminUser()->id,
            'mediaRoleIds' => Role::ROLE_VISITOR_ID,
            'mediaType' => Media::TYPE_SERIES,
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
                    'resourceAlbumUrl' => 'http://www.test1.com',
                ]),
            ],
        ]);

        $mediaQueue = $mediaQueueService->createMediaSeriesQueue($dto);

        /**
         * @var AlbumQueue $albumQueue
         */
        $albumQueue = $mediaQueue->albumQueues->first();
        $albumQueue->status = AlbumQueue::STATUS_STARTED;
        $albumQueue->save();

        //updateMediaQueueStatusOfVideoQueue is triggered by VideoQueueUpdatedEventHandler
        $mediaQueue->refresh();

        $this->assertEquals(MediaQueue::STATUS_STARTED, $mediaQueue->status);
        $this->assertNull($mediaQueue->media_id);

        $album = $this->createAlbum();
        $albumQueue->status = AlbumQueue::STATUS_COMPLETED;
        $albumQueue->album_id = $album->id;
        $albumQueue->save();
        $albumQueue->refresh();
        $this->assertNull($mediaQueue->media_id);


        /**
         * @var VideoQueue $videoQueue
         */
        $videoQueue = $mediaQueue->videoQueues->sortByDesc('id')->first();

        $video = $this->createVideo();
        $videoQueue->status = VideoQueue::STATUS_COMPLETED;
        $videoQueue->video_id = $video->id;
        $videoQueue->save();
        $videoQueue->refresh();

        $mediaQueue->refresh();

        $this->assertNotNull($mediaQueue->media_id);
        $this->assertEquals(Media::TYPE_SERIES, $mediaQueue->media->type);
    }
}
