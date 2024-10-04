<?php

namespace App\Services;

use App\Dtos\ResourceAlbumDto;
use App\Models\ResourceActor;
use App\Models\ResourceAlbum;
use App\Models\ResourceAlbumActor;
use App\Models\ResourceAlbumCategory;
use App\Models\ResourceAlbumFile;
use App\Models\ResourceAlbumTag;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;

class ResourceAlbumService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function updateOrCreateResourceAlbum(ResourceAlbumDto $dto): ResourceAlbum
    {
        /**
         * @var ResourceAlbum $resourceAlbum
         */
        $resourceAlbum = ResourceAlbum::updateOrCreate([
            'id' => $dto->resourceAlbumId
        ], [
            'name' => $dto->name,
            'description' => $dto->description,
            'resource_id' => $dto->resourceId,
            'resource_album_url' => $dto->resourceAlbumUrl,
            'thumbnail_file_id' => $dto->thumbnailFileDto ? $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id : null,
            'download_file_id' => $dto->downloadFileDto ? $this->fileService->getOrCreateFile($dto->downloadFileDto)->id : null,
            'meta_json' => $dto->metaJson,
        ]);

        if (isset($dto->resourceActorDtos)) {
            ResourceAlbumFile::query()
                ->where('resource_album_id', $resourceAlbum->id)
                ->delete();

            foreach ($dto->resourceAlbumFileDtos as $resourceAlbumFileDto) {
                $resourceAlbumFileId = $this->fileService->getOrCreateFile($resourceAlbumFileDto)->id;
                ResourceAlbumFile::query()->create([
                    'resource_album_id' => $resourceAlbum->id,
                    'file_id' => $resourceAlbumFileId,
                ]);
            }

            if (empty($resourceAlbum->thumbnail_file_id)) {
                $firstImage = ResourceAlbumFile::query()
                    ->where('resource_album_id', $resourceAlbum->id)
                    ->first();
                $resourceAlbum->thumbnail_file_id = $firstImage->file_id;
                $resourceAlbum->save();
            }

            //TODO zip all album files here
        }

        if (isset($dto->resourceTagDtos)) {
            ResourceAlbumTag::query()
                ->where('resource_album_id', $resourceAlbum->id)
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

                ResourceAlbumTag::query()->create([
                    'resource_album_id' => $resourceAlbum->id,
                    'resource_tag_id' => $resourceTag->id,
                ]);
            }
        }

        if (isset($dto->resourceActorDtos)) {
            ResourceAlbumActor::query()
                ->where('resource_album_id', $resourceAlbum->id)
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
                    'country' => $resourceActorDto->country,
                ]);

                ResourceAlbumActor::query()->create([
                    'resource_album_id' => $resourceAlbum->id,
                    'resource_actor_id' => $resourceActor->id,
                ]);
            }
        }

        if (isset($dto->resourceCategoryDtos)) {

            ResourceAlbumCategory::query()
                ->where('resource_album_id', $resourceAlbum->id)
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

                ResourceAlbumCategory::query()->create([
                    'resource_album_id' => $resourceAlbum->id,
                    'resource_category_id' => $resourceCategory->id,
                ]);
            }
        }

        return $resourceAlbum;
    }

}
