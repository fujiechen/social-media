<?php

use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\TagDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\File;
use App\Models\ResourceTag;
use App\Models\Video;
use App\Models\VideoTag;
use App\Services\ResourceVideoService;
use App\Services\TagService;
use App\Services\VideoService;
use Tests\TestCase;

class ResourceTagServiceTest extends TestCase
{
    public function testBuildVideoTagsFromResourceTags(): void {
        /**
         * 1. create a resource video with resource tags
         * 2. create a video from resource video
         * 3. assign resource tags to a tag
         * 4. check step 2 video has step 3 tag
         *
         * 5. assign resource tags to another tag
         * 6. check step 2 video has step 5 tag
         */

        $resourceTagName1 = $this->faker()->name;
        $resourceTagName2 = $this->faker()->name;
        $resourceTagName3 = $this->faker()->name;

        $tagName1 = $this->faker()->name;
        $tagName2 = $this->faker()->name;

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
            'resourceTagDtos' => [
                new ResourceTagDto(['name' => $resourceTagName1]),
                new ResourceTagDto(['name' => $resourceTagName2]),
                new ResourceTagDto(['name' => $resourceTagName3]),
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

        $this->assertEmpty($video->tags);

        /**
         * @var TagService $tagService
         */
        $tagService = app(TagService::class);

        $resourceTag1 = ResourceTag::where('name', '=', $resourceTagName1)->first();
        $resourceTag2 = ResourceTag::where('name', '=', $resourceTagName2)->first();
        $resourceTag3 = ResourceTag::where('name', '=', $resourceTagName3)->first();

        //create
        $tag1 = $tagService->updateOrCreateTag(new TagDto([
            'name' => $tagName1,
            'resourceTagIds' => [$resourceTag1->id, $resourceTag2->id, $resourceTag3->id],
        ]));

        $video->refresh();
        $this->assertEquals(1, $video->tags()->count());
        $this->assertEquals($tag1->name, $video->tags()->first()->name);

        //update
        $tag2 = $tagService->updateOrCreateTag(new TagDto([
            'name' => $tagName2,
            'resourceTagIds' => [$resourceTag2->id],
        ]));

        $video->refresh();
        $this->assertEquals(2, $video->tags()->count());

        $this->assertEquals(1,
            VideoTag::where('video_id', '=', $video->id)->where('tag_id', '=', $tag1->id)->count());
        $this->assertEquals(1,
            VideoTag::where('video_id', '=', $video->id)->where('tag_id', '=', $tag2->id)->count());
    }

}
