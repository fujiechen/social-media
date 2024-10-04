<?php

use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Models\ResourceActor;
use App\Services\ResourceVideoService;
use Tests\TestCase;

class ResourceVideoServiceTest extends TestCase
{
    public function testUpdateOrCreateResourceVideo(): void {
        $resource = $this->createResource();

        //test creation
        /**
         * @var ResourceVideoService $resourceVideoService
         */
        $resourceVideoService = app(ResourceVideoService::class);

        $resourceVideo = $resourceVideoService->updateOrCreateResourceVideo(new ResourceVideoDto([
            'name' => 'test resource video',
            'description' => 'test description',
            'resourceId' => $resource->id,
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
            'resourceTagDtos' => [
                new ResourceTagDto(['name' => 'tag1']),
                new ResourceTagDto(['name' => 'tag2']),
                new ResourceTagDto(['name' => 'tag3']),
            ],
            'resourceActorDtos' => [
                new ResourceActor(['name' => 'actor1', 'country'=> 'CN']),
            ],
            'resourceCategoryDtos' => [
                new ResourceCategoryDto(['name' => 'cat1']),
                new ResourceCategoryDto(['name' => 'cat2']),
            ],
        ]));

        $this->assertEquals('test resource video', $resourceVideo->name);
        $this->assertEquals('test description', $resourceVideo->description);
        $this->assertEquals($resource->jsonSerialize(), $resourceVideo->resource->jsonSerialize());
        $this->assertEquals(3, $resourceVideo->resourceTags()->count());
        $this->assertEquals(2, $resourceVideo->resourceCategories()->count());
        $this->assertEquals(1, $resourceVideo->resourceActors()->count());


        //test update
        $resource = $this->createResource();
        $resourceVideo = $resourceVideoService->updateOrCreateResourceVideo(new ResourceVideoDto([
            'resourceVideoId' => $resourceVideo->id,
            'name' => 'test resource video 1',
            'description' => 'test description 1',
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


        $this->assertEquals('test resource video 1', $resourceVideo->name);
        $this->assertEquals('test description 1', $resourceVideo->description);
        $this->assertEquals($resource->jsonSerialize(), $resourceVideo->resource->jsonSerialize());
        $this->assertEquals(2, $resourceVideo->resourceTags()->count());
        $this->assertEquals(1, $resourceVideo->resourceCategories()->count());
        $this->assertEquals(1, $resourceVideo->resourceActors()->count());
        $this->assertEquals(1, $resourceVideo->resourceTags()->where('name', '=', 'tag3')->count());
        $this->assertEquals(1, $resourceVideo->resourceTags()->where('name', '=', 'tag4')->count());
        $this->assertEquals(1, $resourceVideo->resourceActors()->where('name', '=', 'actor1')->count());
        $this->assertEquals(1, $resourceVideo->resourceActors()->where('country', '=', 'CA')->count());
        $this->assertEquals(1, $resourceVideo->resourceCategories()->where('name', '=', 'cat2')->count());
    }

}
