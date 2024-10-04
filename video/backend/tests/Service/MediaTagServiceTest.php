<?php

use App\Dtos\MediaDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\TagDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\File;
use App\Models\Media;
use App\Models\MediaTag;
use App\Models\ResourceTag;
use App\Models\Role;
use App\Models\Video;
use App\Services\MediaService;
use App\Services\ResourceVideoService;
use App\Services\TagService;
use App\Services\VideoService;
use Tests\TestCase;

class MediaTagServiceTest extends TestCase
{
    /**
     * Add or Update VideoTag and it should update related MediaTag
     * @return void
     */
    public function testMediaVideoTagChanged(): void {
        /**
         * 1. create resource video with resource tags
         * 2. create video with step 1 resource
         * 3. create media video with step 2 video
         * 4. create tag and attach resource tags from step 1 to video from step 2
         * 5. check media tag has step 4 tag
         *
         * 6. attach another tag to step 1 resource video tag
         * 7. check media tag has both step 4 tag and step 6 tag
         *
         * 8. remove step 6 tag
         * 9. check media tag has step 4 tag only
         */

        /**
         * @var ResourceVideoService $resourceVideoService
         */
        $resourceVideoService = app(ResourceVideoService::class);

        $resourceTagName1 = $this->faker()->name;
        $resourceTagName2 = $this->faker()->name;
        $resourceTagName3 = $this->faker()->name;

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

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $media = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $this->assertEquals('test resource video', $media->name);
        $this->assertEquals('test description', $media->description);
        $this->assertTrue($media->isVideo());
        $this->assertEquals($video->id, $media->mediaable->id);


        $resourceTag1 = ResourceTag::query()->where('name', '=', $resourceTagName1)->first();
        $resourceTag2 = ResourceTag::query()->where('name', '=', $resourceTagName2)->first();
        $resourceTag3 = ResourceTag::query()->where('name', '=', $resourceTagName3)->first();

        $tagName1 = $this->faker()->name;
        $tagName2 = $this->faker()->name;

        /**
         * @var TagService $tagService
         */
        $tagService = app(abstract: TagService::class);
        $tag1 = $tagService->updateOrCreateTag(new TagDto([
            'name' => $tagName1,
            'resourceTagIds' => [$resourceTag1->id, $resourceTag2->id, $resourceTag3->id],
        ]));

        $this->assertEquals(1, $media->tags()->count());
        $this->assertEquals(1, MediaTag::query()->where('media_id', '=', $media->id)
            ->where('tag_id', '=', $tag1->id)->count());


        $tag2 = $tagService->updateOrCreateTag(dto: new TagDto([
            'name' => $tagName2,
            'resourceTagIds' => [$resourceTag2->id],
        ]));

        $this->assertEquals(2, $media->tags()->count());
        $this->assertEquals(1, MediaTag::query()->where('media_id', '=', $media->id)
            ->where('tag_id', '=', $tag1->id)->count());
        $this->assertEquals(1, MediaTag::query()->where('media_id', '=', $media->id)
            ->where('tag_id', '=', $tag2->id)->count());
    }

}
