<?php

use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Actor;
use App\Models\Category;
use App\Models\File;
use App\Models\ResourceActor;
use App\Models\Tag;
use App\Models\Video;
use App\Services\ResourceVideoService;
use App\Services\VideoService;
use Tests\TestCase;

/**
 * update create video
 * - from upload & cloud
 * - from resource
 */
class VideoServiceTest extends TestCase
{

    public function testUpdateOrCreateVideoFromUpload(): void {

        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);

        $tag1 = Tag::create(['name' => 'tag1']);
        $tag2 = Tag::create(['name' => 'tag2']);
        $tag3 = Tag::create(['name' => 'tag3']);

        $actor1 = Actor::create(['name' => 'actor1', 'country' => 'CN']);
        $category1 = Category::create(['name' => 'cat1']);

        //create
        $dto = new VideoDto([
            'type' => Video::TYPE_UPLOAD,
            'name' => 'test video',
            'description' => 'video description',
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
            'tagIds' => [$tag1->id, $tag2->id, $tag3->id],
            'categoryIds' => [$category1->id],
            'actorIds' => [$actor1->id],
        ]);

        $video = $videoService->updateOrCreateVideo($dto);

        $this->assertEquals('test video', $video->name);
        $this->assertEquals('video description', $video->description);
        $this->assertEquals(File::TYPE_PRIVATE_BUCKET, $video->videoFile->bucket_type);
        $this->assertEquals(File::TYPE_PUBLIC_BUCKET, $video->previewFile->bucket_type);
        $this->assertEquals(File::TYPE_PUBLIC_BUCKET, $video->thumbnailFile->bucket_type);
        $this->assertEquals(3, $video->tags()->count());
        $this->assertEquals(1, $video->categories()->count());
        $this->assertEquals(1, $video->actors()->count());

        // update
        $tag4 = Tag::create(['name' => 'tag4']);
        $actor1->country = 'CA';
        $actor1->save();

        $dto = new VideoDto([
            'videoId' => $video->id,
            'type' => Video::TYPE_UPLOAD,
            'name' => 'test video 1',
            'description' => 'video description 1',
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
            'tagIds' => [$tag1->id, $tag4->id],
            'categoryIds' => [],
            'actorIds' => [$actor1->id],
        ]);

        $video = $videoService->updateOrCreateVideo($dto);

        $this->assertEquals('test video 1', $video->name);
        $this->assertEquals('video description 1', $video->description);
        $this->assertEquals(2, $video->tags()->count());
        $this->assertEquals(1, $video->tags()->where('name', '=', 'tag1')->count());
        $this->assertEquals(1, $video->tags()->where('name', '=', 'tag4')->count());
        $this->assertEquals(0, $video->categories()->count());
        $this->assertEquals(1, $video->actors()->count());
        $this->assertEquals(1, $video->actors()->where('name', '=', 'actor1')->count());
        $this->assertEquals(1, $video->actors()->where('country', '=', 'CA')->count());

    }

    public function testCreateVideoFromResource(): void {

        $resource = $this->createResource();
        /**
         * @var ResourceVideoService $resourceVideoService
         */
        $resourceVideoService = app(ResourceVideoService::class);
        $resourceVideo = $resourceVideoService->updateOrCreateResourceVideo(new ResourceVideoDto([
            'name' => 'test resource video',
            'description' => 'test description',
            'resourceId' => $resource->id,
            'resourceVideoUrl' => 'http://www.test1.com',
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
            'resourceTagDtos' => [
                new ResourceTagDto(['name' => 'tag3']),
                new ResourceTagDto(['name' => 'tag4']),
            ],
            'resourceActorDtos' => [
                new ResourceActor(['name' => 'actor1', 'country'=> 'CA']),
            ],
            'resourceCategoryDtos' => [
                new ResourceCategoryDto(['name' => 'cat2']),
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

        $this->assertEquals('test resource video', $video->name);
        $this->assertEquals('test description', $video->description);
        $this->assertEquals($resourceVideo->id, $video->resourceVideo->id);
        $this->assertEquals($resourceVideo->previewFile->id, $video->previewFile->id);
        $this->assertEquals($resourceVideo->thumbnailFile->id, $video->thumbnailFile->id);
        $this->assertEquals($resourceVideo->file->id, $video->videoFile->id);
        $this->assertEmpty($video->tags);
        $this->assertEmpty($video->categories);
        $this->assertEmpty($video->actors);
    }

}
