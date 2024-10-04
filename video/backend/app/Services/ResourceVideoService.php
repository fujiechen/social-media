<?php

namespace App\Services;

use App\Dtos\ResourceVideoDto;
use App\Models\ResourceActor;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use App\Models\ResourceVideo;
use App\Models\ResourceVideoActor;
use App\Models\ResourceVideoCategory;
use App\Models\ResourceVideoTag;

class ResourceVideoService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function updateOrCreateResourceVideo(ResourceVideoDto $dto): ResourceVideo
    {
        $resourceVideo = ResourceVideo::updateOrCreate([
            'id' => $dto->resourceVideoId
        ], [
            'name' => $dto->name,
            'description' => $dto->description,
            'duration_in_seconds' => $dto->durationInSeconds,
            'resource_id' => $dto->resourceId,
            'resource_video_url' => $dto->resourceVideoUrl,
            'file_id' => $this->fileService->getOrCreateFile($dto->videoFileDto)->id,
            'thumbnail_file_id' => $dto->thumbnailFileDto ? $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id: null,
            'preview_file_id' => $dto->previewFileDto ? $this->fileService->getOrCreateFile($dto->previewFileDto)->id : null,
            'download_file_id' => $dto->downloadFileDto ? $this->fileService->getOrCreateFile($dto->downloadFileDto)->id : null,
            'meta_json' => $dto->metaJson,
        ]);

        if (isset($dto->resourceTagDtos)) {
            ResourceVideoTag::query()
                ->where('resource_video_id', $resourceVideo->id)
                ->delete();

            foreach ($dto->resourceTagDtos as $resourceTagDto) {
                /**
                 * @var ResourceTag $resourceTag
                 */
                $resourceTag = ResourceTag::query()->updateOrCreate([
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceTagDto->name
                ], [
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceTagDto->name
                ]);

                ResourceVideoTag::query()->create([
                    'resource_video_id' => $resourceVideo->id,
                    'resource_tag_id' => $resourceTag->id,
                ]);
            }
        }

        if (isset($dto->resourceActorDtos)) {
            ResourceVideoActor::query()
                ->where('resource_video_id', $resourceVideo->id)
                ->delete();

            foreach ($dto->resourceActorDtos as $resourceActorDto) {
                /**
                 * @var ResourceActor $resourceActor
                 */
                $resourceActor = ResourceActor::query()->updateOrCreate([
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceActorDto->name
                ], [
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceActorDto->name,
                ]);

                ResourceVideoActor::query()->create([
                    'resource_video_id' => $resourceVideo->id,
                    'resource_actor_id' => $resourceActor->id,
                ]);
            }
        }

        if (isset($dto->resourceCategoryDtos)) {

            ResourceVideoCategory::query()
                ->where('resource_video_id', $resourceVideo->id)
                ->delete();

            foreach ($dto->resourceCategoryDtos as $resourceCategoryDto) {
                /**
                 * @var ResourceCategory $resourceCategory
                 */
                $resourceCategory = ResourceCategory::query()->updateOrCreate([
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceCategoryDto->name
                ], [
                    'resource_id' => $dto->resourceId,
                    'name' => $resourceCategoryDto->name
                ]);

                ResourceVideoCategory::query()->create([
                    'resource_video_id' => $resourceVideo->id,
                    'resource_category_id' => $resourceCategory->id,
                ]);
            }
        }

        return $resourceVideo;
    }

}
