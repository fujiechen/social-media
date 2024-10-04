<?php

namespace App\Transformers;

use App\Models\Album;
use App\Models\Media;
use App\Models\Series;
use App\Models\Video;
use League\Fractal\TransformerAbstract;

class MediaTransformer extends TransformerAbstract
{
    private UserTransformer $userTransformer;
    private TagTransformer $tagTransformer;
    private CategoryTransformer $categoryTransformer;
    private ActorTransformer $actorTransformer;
    private FileTransformer $fileTransformer;

    public function __construct(
        UserTransformer     $userTransformer,
        TagTransformer      $tagTransformer,
        CategoryTransformer $categoryTransformer,
        ActorTransformer    $actorTransformer,
        FileTransformer     $fileTransformer,
    )
    {
        $this->userTransformer = $userTransformer;
        $this->tagTransformer = $tagTransformer;
        $this->categoryTransformer = $categoryTransformer;
        $this->actorTransformer = $actorTransformer;
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(Media $media): array
    {
        $tags = [];
        foreach ($media->tags as $tag) {
            $tags[] = $this->tagTransformer->transform($tag);
        }

        $categories = [];
        foreach ($media->categories as $category) {
            $categories[] = $this->categoryTransformer->transform($category);
        }

        $actors = [];
        foreach ($media->actors as $actor) {
            $actors[] = $this->actorTransformer->transform($actor);
        }

        $userData = $this->userTransformer->transform($media->user);
        unset($userData['username']);

        $data = [
            'id' => $media->id,
            'name' => $media->name,
            'description' => $media->description,
            'type' => $media->type,
            'media_permissions' => $media->permissions,
            'media_permission_role_ids' => $media->role_ids,
            'parent_media_id' => $media->parent_media_id,
            'tags' => $tags,
            'categories' => $categories,
            'actors' => $actors,
            'created_at' => $media->updated_at, //use last update timestamp
            'user' => $userData,
            'media_meta' => [],
        ];

        if ($media->isSeries()) {
            /**
             * @var Series $series
             */
            $series = $media->mediaable;;
            $data['thumbnail_file'] = $series->thumbnailFile ? $this->fileTransformer->transform($series->thumbnailFile) : null;
            foreach ($media->childrenMedias as $videoMedia) {
                $data['children_medias'][] = $this->transform($videoMedia);
            }
        } else if ($media->isAlbum()) {
            /**
             * @var Album $album
             */
            $album = $media->mediaable;

            $data['thumbnail_file'] = $album->thumbnailFile ? $this->fileTransformer->transform($album->thumbnailFile) : null;
            $data['total_images'] = $album->images->count();
        } else if ($media->isVideo()) {
            /**
             * @var Video $video
             */
            $video = $media->mediaable;
            $data['media_meta'] = $video->meta_json;
            $data['duration_in_seconds'] = $video->duration_in_seconds;
            $data['thumbnail_file'] = $video->thumbnailFile ? $this->fileTransformer->transform($video->thumbnailFile) : null;
            $data['preview_file'] = $video->previewFile ? $this->fileTransformer->transform($video->previewFile) : null;
        }

        return $data;
    }
}
