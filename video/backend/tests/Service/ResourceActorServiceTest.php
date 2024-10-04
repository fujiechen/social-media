<?php

use App\Dtos\ResourceActorDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\ActorDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Actor;
use App\Models\File;
use App\Models\ResourceActor;
use App\Models\Video;
use App\Models\VideoActor;
use App\Services\ResourceVideoService;
use App\Services\ActorService;
use App\Services\VideoService;
use Tests\TestCase;

class ResourceActorServiceTest extends TestCase
{
    public function testBuildVideoActorsFromResourceActors(): void {
        /**
         * 1. create a resource video with resource actors
         * 2. create a video from resource video
         * 3. assign resource actors to a actor
         * 4. check step 2 video has step 3 actor
         *
         * 5. assign resource actors to another actor
         * 6. check step 2 video has step 5 actor
         */

        $resourceActorName1 = $this->faker()->name;
        $resourceActorName2 = $this->faker()->name;
        $resourceActorName3 = $this->faker()->name;

        $actorName1 = $this->faker()->name;
        $actorName2 = $this->faker()->name;

        /**
         * @var ResourceVideoService $resourceVideoService
         */
        $resourceVideoService = app(ResourceVideoService::class);

        $resourceVideo = $resourceVideoService->updateOrCreateResourceVideo(new ResourceVideoDto([
            'name' => 'test resource video',
            'description' => 'test description',
            'resourceId' => $this->createResource()->id,
            'resourceVideoUrl' => 'http://www.test.com',
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PRIVATE_BUCKET,
                'uploadPath' => '',
            ]),
            'resourceActorDtos' => [
                new ResourceActorDto(['name' => $resourceActorName1]),
                new ResourceActorDto(['name' => $resourceActorName2]),
                new ResourceActorDto(['name' => $resourceActorName3]),
            ],
        ]));

        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);

        $video = $videoService->updateOrCreateVideo(new VideoDto([
            'type' => Video::TYPE_RESOURCE,
            'resourceVideoId' => $resourceVideo->id,
        ]));

        $this->assertEmpty($video->actors);

        /**
         * @var ActorService $actorService
         */
        $actorService = app(ActorService::class);

        $resourceActor1 = ResourceActor::where('name', '=', $resourceActorName1)->first();
        $resourceActor2 = ResourceActor::where('name', '=', $resourceActorName2)->first();
        $resourceActor3 = ResourceActor::where('name', '=', $resourceActorName3)->first();

        //create
        $actor1 = $actorService->updateOrCreateActor(new ActorDto([
            'name' => $actorName1,
            'resourceActorIds' => [$resourceActor1->id, $resourceActor2->id, $resourceActor3->id],
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $video->refresh();
        $this->assertEquals(1, $video->actors()->count());
        $this->assertEquals($actor1->name, $video->actors()->first()->name);

        //update
        $actor2 = $actorService->updateOrCreateActor(new ActorDto([
            'name' => $actorName2,
            'resourceActorIds' => [$resourceActor2->id],
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $video->refresh();
        $this->assertEquals(2, $video->actors()->count());

        $this->assertEquals(1,
            VideoActor::where('video_id', '=', $video->id)->where('actor_id', '=', $actor1->id)->count());
        $this->assertEquals(1,
            VideoActor::where('video_id', '=', $video->id)->where('actor_id', '=', $actor2->id)->count());
    }

}
