<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoCategory;

class ResourceCategoryService
{
    /**
     * WHen Resource Category is assigned a Category, build all related videos categories
     *
     * @param int $resourceCategoryId
     * @param int|null $originalCategoryId
     * @return bool
     */
    public function buildVideoCategoriesFromResourceCategories(int $resourceCategoryId, ?int $originalCategoryId): bool {
        //delete all categories from original category id
        if ($originalCategoryId) {
            VideoCategory::query()
                ->where('category_id', '=', $originalCategoryId)
                ->delete();
        }

        $videos = Video::query()
            ->select('videos.*')
            ->join('resource_videos', 'resource_videos.id', '=', 'videos.resource_video_id')
            ->join('resource_video_categories', 'resource_video_categories.resource_video_id', '=', 'resource_videos.id')
            ->where('resource_video_categories.resource_category_id', '=', $resourceCategoryId);

        $videos->chunk(100, function ($chunk) {
            /**
             * @var Video $video
             */
            foreach ($chunk as $video) {
                //delete all resource categories
                VideoCategory::query()
                    ->leftJoin('resource_categories', 'resource_categories.category_id', '=', 'video_categories.category_id')
                    ->whereNotNull('resource_categories.category_id')
                    ->where('video_categories.video_id', '=', $video->id)
                    ->delete();

                //create categories for this video
                foreach ($video->resourceVideo->resourceCategories as $resourceCategory) {
                    if (empty($resourceCategory->category_id)) {
                        continue;
                    }

                    VideoCategory::query()->firstOrCreate([
                        'video_id' => $video->id,
                        'category_id' => $resourceCategory->category_id,
                    ], [
                        'video_id' => $video->id,
                        'category_id' => $resourceCategory->category_id,
                    ]);
                }
            }
        });

        return true;
    }
}
