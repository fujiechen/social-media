<?php

namespace App\Services;

use App\Dtos\AlbumDto;
use App\Models\Album;
use App\Models\AlbumActor;
use App\Models\AlbumCategory;
use App\Models\AlbumFile;
use App\Models\AlbumTag;
use App\Models\Media;
use App\Models\ResourceAlbum;
use Illuminate\Support\Facades\DB;

class AlbumService
{
    private MediaService $mediaService;
    private FileService $fileService;

    public function __construct(MediaService $mediaService, FileService $fileService) {
        $this->mediaService = $mediaService;
        $this->fileService = $fileService;
    }

    /**
     * Create for type upload, resource, cloud
     * Update for cloud only
     *
     * @param AlbumDto $dto
     * @return Album
     */
    public function updateOrCreateAlbum(AlbumDto $dto): Album
    {
        return DB::transaction(function() use ($dto) {
            if ($dto->type == Album::TYPE_RESOURCE) {
                /**
                 * @var ResourceAlbum $resourceAlbum
                 */
                $resourceAlbum = ResourceAlbum::query()->find($dto->resourceAlbumId);

                if (empty($dto->name)) {
                    $albumName = $resourceAlbum->name;
                } else {
                    $albumName = $dto->name;
                }

                if (empty($dto->description)) {
                    $albumDescription = $resourceAlbum->description;
                } else {
                    $albumDescription = $dto->description;
                }

                /**
                 * @var Album $album
                 */
                $album = Album::updateOrCreate([
                    'id' => $dto->albumId,
                ], [
                    'type' => Album::TYPE_CLOUD, //after choose resource, it became cloud
                    'name' => $albumName,
                    'description' => $albumDescription,
                    'thumbnail_file_id' => $resourceAlbum->thumbnailFile?->id,
                    'download_file_id' => $resourceAlbum->downloadFile?->id,
                    'resource_album_id' => $dto->resourceAlbumId,
                    'meta_json' => $resourceAlbum->meta_json,
                ]);

                AlbumFile::query()->where('album_id', '=', $album->id)->delete();
                foreach ($resourceAlbum->resourceAlbumFiles as $resourceAlbumFile) {
                    AlbumFile::create([
                        'album_id' => $album->id,
                        'file_id' => $resourceAlbumFile->file_id,
                    ]);
                }
            } else { // upload or cloud

                /**
                 * @var Album $album
                 */
                $album = Album::updateOrCreate([
                    'id' => $dto->albumId,
                ], [
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'thumbnail_file_id' => $dto->thumbnailFileDto ? $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id : null,
                    'download_file_id' => $dto->downloadFileDto ? $this->fileService->getOrCreateFile($dto->downloadFileDto)->id : null,
                    'meta_json' => $dto->metaJson,
                ]);

                AlbumFile::query()->where('album_id', '=', $album->id)->delete();

                foreach ($dto->imageFileDtos as $imageFileDto) {
                    if ($dto->type !== Album::TYPE_CLOUD) {
                        $image = $this->fileService->getOrCreateFile($imageFileDto);
                        $imageFileDto->fileId = $image->id;
                    }

                    AlbumFile::create([
                        'album_id' => $album->id,
                        'file_id' => $imageFileDto->fileId,
                    ]);
                }

                if (empty($album->thumbnail_file_id)) {
                    $firstImage = AlbumFile::query()
                        ->where('album_id', $album->id)
                        ->first();
                    $album->thumbnail_file_id = $firstImage->file_id;
                    $album->save();
                }
            }

            $album->albumTags()->delete();
            foreach ($dto->tagIds as $tagId) {
                AlbumTag::query()->create([
                    'album_id' => $album->id,
                    'tag_id' => $tagId,
                ]);
            }

            $album->albumCategories()->delete();
            foreach ($dto->categoryIds as $categoryId) {
                AlbumCategory::query()->create([
                    'album_id' => $album->id,
                    'category_id' => $categoryId,
                ]);
            }


            $album->albumActors()->delete();
            foreach ($dto->actorIds as $actorId) {
                AlbumActor::query()->create([
                    'album_id' => $album->id,
                    'actor_id' => $actorId,
                ]);
            }


            return $album;
        });
    }

    public function postDeleted(Album $album): void {
        foreach ($album->medias as $media) {
            $media->delete();
        }
    }

    /**
     * @param int $albumId
     * @return void
     */
    public function postUpdated(int $albumId): void {
        /**
         * @var Album $album
         */
        $album = Album::find($albumId);

        /**
         * @var Media $media
         */
        foreach ($this->mediaService->fetchAllMediasByAlbum($albumId) as $media) {
            if ($album->name != $media->name) {
                $media->name = $album->name;
            }

            if ($album->description != $media->description) {
                $media->description = $album->description;
            }

            /**
             * This will rerun all tags, actors, categories and searches
             */
            $media->save();
        }
    }
}
