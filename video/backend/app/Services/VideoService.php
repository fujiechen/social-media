<?php

namespace App\Services;

use App\Dtos\VideoDto;
use App\Models\Media;
use App\Models\ResourceVideo;
use App\Models\Video;
use App\Models\VideoActor;
use App\Models\VideoCategory;
use App\Models\VideoTag;
use Illuminate\Support\Facades\DB;

class VideoService
{

    private FileService $fileService;
    private MediaService $mediaService;

    public function __construct(FileService $fileService, MediaService $mediaService)
    {
        $this->fileService = $fileService;
        $this->mediaService = $mediaService;
    }

    /**
     * Create for type upload, resource, cloud
     * Update for cloud only
     *
     * @param VideoDto $dto
     * @return Video
     */
    public function updateOrCreateVideo(VideoDto $dto): Video
    {
        return DB::transaction(function () use ($dto) {
            if ($dto->type == Video::TYPE_CLOUD) { //update or create
                $video = Video::updateOrCreate([
                    'id' => $dto->videoId,
                ], [
                    'type' => $dto->type,
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'duration_in_seconds' => $dto->durationInSeconds,
                    'thumbnail_file_id' => $dto->thumbnailFileDto->fileId,
                    'video_file_id' => $dto->videoFileDto->fileId,
                    'preview_file_id' => $dto->previewFileDto?->fileId,
                    'download_file_id' => $dto->downloadFileDto?->fileId,
                    'meta_json' => $dto->metaJson,
                ]);
            } else if ($dto->type == Video::TYPE_UPLOAD) { //create only
                $video = Video::updateOrCreate([
                    'id' => $dto->videoId,
                ], [
                    'type' => Video::TYPE_CLOUD, //after uploads, it became cloud
                    'name' => $dto->name,
                    'description' => $dto->description,
                    'duration_in_seconds' => $dto->durationInSeconds,
                    'thumbnail_file_id' => $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id,
                    'video_file_id' => $this->fileService->getOrCreateFile($dto->videoFileDto)->id,
                    'preview_file_id' => $dto->previewFileDto ? $this->fileService->getOrCreateFile($dto->previewFileDto)->id : null,
                    'download_file_id' => $dto->downloadFileDto ? $this->fileService->getOrCreateFile($dto->downloadFileDto)->id : null,
                    'meta_json' => $dto->metaJson,
                ]);


            } else if ($dto->type == Video::TYPE_RESOURCE) { //create only

                /**
                 * @var ResourceVideo $resourceVideo
                 */
                $resourceVideo = ResourceVideo::query()->find($dto->resourceVideoId);

                if (empty($dto->name)) {
                    $videoName = $resourceVideo->name;
                } else {
                    $videoName = $dto->name;
                }

                if (empty($dto->description)) {
                    $videoDescription = $resourceVideo->description;
                } else {
                    $videoDescription = $dto->description;
                }

                /**
                 * @var Video $video
                 */
                $video = Video::updateOrCreate([
                    'id' => $dto->videoId,
                ], [
                    'type' => Video::TYPE_CLOUD, //after choose resource, it became cloud
                    'name' => $videoName,
                    'duration_in_seconds' => $resourceVideo->duration_in_seconds,
                    'description' => $videoDescription,
                    'video_file_id' => $resourceVideo->file->id,
                    'thumbnail_file_id' => $resourceVideo->thumbnailFile?->id,
                    'preview_file_id' => $resourceVideo->previewFile?->id,
                    'download_file_id' => $resourceVideo->downloadFile?->id,
                    'resource_video_id' => $dto->resourceVideoId,
                    'meta_json' => $resourceVideo->meta_json,
                ]);
            }

            $video->videoTags()->delete();

            foreach ($dto->tagIds as $tagId) {
                VideoTag::query()->create([
                    'video_id' => $video->id,
                    'tag_id' => $tagId,
                ]);
            }

            $video->videoActors()->delete();

            foreach ($dto->actorIds as $actorId) {
                VideoActor::query()->create([
                    'video_id' => $video->id,
                    'actor_id' => $actorId,
                ]);
            }

            $video->videoCategories()->delete();

            foreach ($dto->categoryIds as $categoryId) {
                VideoCategory::query()->create([
                    'video_id' => $video->id,
                    'category_id' => $categoryId,
                ]);
            }

            return $video;
        });
    }

    public function postDeleted(Video $video): void {
        foreach ($video->medias as $media) {
            $media->delete();
        }
    }

    /**
     * @param int $videoId
     * @return void
     */
    public function postUpdated(int $videoId): void {
        /**
         * @var Video $video
         */
        $video = Video::find($videoId);

        /**
         * @var Media $media
         */
        foreach ($this->mediaService->fetchAllMediasByVideo($videoId) as $media) {
            if ($video->name != $media->name) {
                $media->name = $video->name;
            }

            if ($video->description != $media->description) {
                $media->description = $video->description;
            }

            /**
             * This will rerun all tags, actors, categories and searches
             */
            $media->save();
        }
    }

}
