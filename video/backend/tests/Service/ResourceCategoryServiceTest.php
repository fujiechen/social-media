<?php

use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\CategoryDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Category;
use App\Models\File;
use App\Models\ResourceCategory;
use App\Models\Video;
use App\Models\VideoCategory;
use App\Services\ResourceVideoService;
use App\Services\CategoryService;
use App\Services\VideoService;
use Tests\TestCase;

class ResourceCategoryServiceTest extends TestCase
{
    public function testBuildVideoCategoriesFromResourceCategories(): void {
        /**
         * 1. create a resource video with resource categories
         * 2. create a video from resource video
         * 3. assign resource categories to a category
         * 4. check step 2 video has step 3 category
         *
         * 5. assign resource categories to another category
         * 6. check step 2 video has step 5 category
         */

        $resourceCategoryName1 = $this->faker()->name;
        $resourceCategoryName2 = $this->faker()->name;
        $resourceCategoryName3 = $this->faker()->name;

        $categoryName1 = $this->faker()->name;
        $categoryName2 = $this->faker()->name;

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
            'resourceCategoryDtos' => [
                new ResourceCategoryDto(['name' => $resourceCategoryName1]),
                new ResourceCategoryDto(['name' => $resourceCategoryName2]),
                new ResourceCategoryDto(['name' => $resourceCategoryName3]),
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

        $this->assertEmpty($video->categories);

        /**
         * @var CategoryService $categoryService
         */
        $categoryService = app(CategoryService::class);

        $resourceCategory1 = ResourceCategory::where('name', '=', $resourceCategoryName1)->first();
        $resourceCategory2 = ResourceCategory::where('name', '=', $resourceCategoryName2)->first();
        $resourceCategory3 = ResourceCategory::where('name', '=', $resourceCategoryName3)->first();

        //create
        $category1 = $categoryService->updateOrCreateCategory(new CategoryDto([
            'name' => $categoryName1,
            'resourceCategoryIds' => [$resourceCategory1->id, $resourceCategory2->id, $resourceCategory3->id],
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $video->refresh();
        $this->assertEquals(1, $video->categories()->count());
        $this->assertEquals($category1->name, $video->categories()->first()->name);

        //update
        $category2 = $categoryService->updateOrCreateCategory(new CategoryDto([
            'name' => $categoryName2,
            'resourceCategoryIds' => [$resourceCategory2->id],
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $video->refresh();
        $this->assertEquals(2, $video->categories()->count());

        $this->assertEquals(1,
            VideoCategory::where('video_id', '=', $video->id)->where('category_id', '=', $category1->id)->count());
        $this->assertEquals(1,
            VideoCategory::where('video_id', '=', $video->id)->where('category_id', '=', $category2->id)->count());
    }

}
