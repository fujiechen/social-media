<?php

namespace App\Services;

use App\Dtos\CategoryDto;
use App\Events\Category\AddCategoryViewCountEvent;
use App\Events\ResourceCategoryAddCategoryEvent;
use App\Events\ResourceCategoryRemoveCategoryEvent;
use App\Models\Album;
use App\Models\Category;
use App\Models\Media;
use App\Models\MediaCategory;
use App\Models\ResourceCategory;
use App\Models\Series;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function getCategoryAndIncreaseCount(int $categoryId): Category {
        /**
         * @var Category $category
         */
        $category = Category::find($categoryId);

        event(new AddCategoryViewCountEvent($category));

        return $category;
    }

    public function updateOrCreateCategory(CategoryDto $dto): Category {
        return DB::transaction(function() use ($dto) {
            $avatarFileId = null;
            if ($dto->type == Category::TYPE_CLOUD) {
                $avatarFileId = $dto->avatarFileDto->fileId;
            } else if ($dto->type == Category::TYPE_UPLOAD) {
                $avatarFileId = $this->fileService->getOrCreateFile($dto->avatarFileDto)->id;
            }

            /**
             * @var Category $category
             */
            $category = Category::query()->updateOrCreate([
                'id' => $dto->categoryId,
            ], [
                'name' => $dto->name,
                'priority' => $dto->priority,
                'avatar_file_id' => $avatarFileId
            ]);

            foreach (ResourceCategory::query()
                         ->where('category_id', '=', $category->id)
                         ->get() as $resourceCategory) {
                $resourceCategory->category_id = null;
                $resourceCategory->save();
                event(new ResourceCategoryRemoveCategoryEvent($resourceCategory->id, $category->id));
            }

            foreach ($dto->resourceCategoryIds as $resourceCategoryId) {
                /**
                 * @var ResourceCategory $resourceCategory
                 */
                $resourceCategory = ResourceCategory::query()->find($resourceCategoryId);
                $resourceCategory->category_id = $category->id;
                $resourceCategory->save();
                event(new ResourceCategoryAddCategoryEvent($resourceCategory->id));
            }

            return $category;
        });
    }

    public function syncActiveMediaCount(Category $category): void {
        $totalActiveVideo = MediaCategory::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_categories.media_id')
            ->where('category_id', '=', $category->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Video::class)
            ->count();

        $totalActiveAlbum = MediaCategory::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_categories.media_id')
            ->where('category_id', '=', $category->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Album::class)
            ->count();

        $totalActiveSeries = MediaCategory::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_categories.media_id')
            ->where('category_id', '=', $category->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Series::class)
            ->count();

        Category::withoutEvents(function () use ($category, $totalActiveVideo, $totalActiveAlbum, $totalActiveSeries) {
            $category->active_media_videos_count = $totalActiveVideo;
            $category->active_media_albums_count = $totalActiveAlbum;
            $category->active_media_series_count = $totalActiveSeries;
            $category->save();
        });
    }
}
