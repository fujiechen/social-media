<?php

use App\Dtos\CategoryDto;
use App\Dtos\UploadFileDto;
use App\Models\Category;
use App\Models\File;
use App\Models\ResourceCategory;
use App\Services\CategoryService;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    public function testUpdateOrCreateCategoryWithoutResourceCategories(): void {
        $dto = new CategoryDto([
            'name' => 'test category',
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        /**
         * @var CategoryService $categoryService
         */
        $categoryService = app(CategoryService::class);
        $category = $categoryService->updateOrCreateCategory($dto);
        $this->assertEquals('test category', $category->name);

        $categoryName1 = $this->faker()->name;
        $dto = new CategoryDto([
            'name' => $categoryName1,
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        $category = $categoryService->updateOrCreateCategory($dto);

        $this->assertEquals($categoryName1, $category->name);
        $this->assertEquals(1,
            Category::query()->where('name', '=', $categoryName1)->count());


        $categoryName2 = $this->faker()->name;
        $dto = new CategoryDto([
            'categoryId' => $category->id,
            'name' => $categoryName2,
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        $category = $categoryService->updateOrCreateCategory($dto);
        $this->assertEquals($categoryName2, $category->name);
        $this->assertEquals(0,
            Category::query()->where('name', '=', $categoryName1)->count());
    }

    public function testUpdateOrCreateCategoryWithResourceCategories(): void {
        /**
         * @var CategoryService $categoryService
         */
        $categoryService = app(CategoryService::class);

        $resourceCategory1 = ResourceCategory::create(['name' => $this->faker()->name]);
        $resourceCategory2 = ResourceCategory::create(['name' => $this->faker()->name]);

        //create
        $category = $categoryService->updateOrCreateCategory(new CategoryDto([
            'name' => $this->faker()->name,
            'resourceCategoryIds' => [$resourceCategory1->id, $resourceCategory2->id],
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $resourceCategory1->refresh();
        $this->assertEquals($category->id, $resourceCategory1->category_id);

        $resourceCategory2->refresh();
        $this->assertEquals($category->id, $resourceCategory2->category_id);

        $this->assertEquals(2, $category->resourceCategories()->count());

        //update
        $category = $categoryService->updateOrCreateCategory(new CategoryDto([
            'categoryId' => $category->id,
            'name' => $this->faker()->name,
            'resourceCategoryIds' => [$resourceCategory1->id],
            'type' => Category::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $this->assertEquals(1, $category->resourceCategories()->count());

        $resourceCategory1->refresh();
        $this->assertEquals($category->id, $resourceCategory1->category_id);

        $resourceCategory2->refresh();
        $this->assertNull($resourceCategory2->category_id);
    }
}
