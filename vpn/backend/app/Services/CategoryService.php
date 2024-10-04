<?php

namespace App\Services;

use App\Dtos\CategoryDto;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function fetchAllCategoriesQuery(): Builder
    {
        return Category::query();
    }

    public function updateOrCreateCategory(CategoryDto $dto): Category {
        return DB::transaction(function() use ($dto) {
            $thumbnailFileId = $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id;

            /**
             * @var Category $category
             */
            $category = Category::query()->updateOrCreate([
                'id' => $dto->categoryId,
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
                'thumbnail_file_id' => $thumbnailFileId,
                'tags' => $dto->tags,
                'highlights' => $dto->highlights,
            ]);

            return $category;
        });
    }
}
