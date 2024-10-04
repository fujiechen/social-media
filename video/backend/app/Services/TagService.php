<?php

namespace App\Services;

use App\Dtos\TagDto;
use App\Events\ResourceTagAddTagEvent;
use App\Events\ResourceTagRemoveTagEvent;
use App\Events\Tag\AddTagViewCountEvent;
use App\Models\Album;
use App\Models\Media;
use App\Models\MediaTag;
use App\Models\ResourceTag;
use App\Models\Series;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class TagService
{
    public function getTagAndIncreaseCount(int $tagId): Tag {
        /**
         * @var Tag $tag
         */
        $tag = Tag::find($tagId);

        event(new AddTagViewCountEvent($tag));

        return $tag;
    }

    public function updateOrCreateTag(TagDto $dto): Tag {
        return DB::transaction(function () use ($dto) {
            if (empty($dto->tagId)) {
                $tag = Tag::query()->firstOrCreate([
                    'name' => $dto->name,
                ], [
                    'name' => $dto->name,
                ]);
            } else {
                $tag = Tag::find($dto->tagId);
                $tag->name = $dto->name;
                $tag->save();
            }

            $tag->priority = $dto->priority;
            $tag->save();

            /**
             * @var ResourceTag $resourceTag
             */
            foreach (ResourceTag::query()
                         ->where('tag_id', '=', $tag->id)
                         ->get() as $resourceTag) {
                $resourceTag->tag_id = null;
                $resourceTag->save();
                event(new ResourceTagRemoveTagEvent($resourceTag->id, $tag->id));
            }

            foreach ($dto->resourceTagIds as $resourceTagId) {
                /**
                 * @var ResourceTag $resourceTag
                 */
                $resourceTag = ResourceTag::query()->find($resourceTagId);
                $resourceTag->tag_id = $tag->id;
                $resourceTag->save();
                event(new ResourceTagAddTagEvent($resourceTag->id));
            }

            return $tag;
        });
    }

    public function syncActiveMediaCount(Tag $tag): void {
        $totalActiveVideo = MediaTag::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_tags.media_id')
            ->where('tag_id', '=', $tag->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Video::class)
            ->count();

        $totalActiveAlbum = MediaTag::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_tags.media_id')
            ->where('tag_id', '=', $tag->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Album::class)
            ->count();

        $totalActiveSeries = MediaTag::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_tags.media_id')
            ->where('tag_id', '=', $tag->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Series::class)
            ->count();

        Tag::withoutEvents(function () use ($tag, $totalActiveVideo, $totalActiveAlbum, $totalActiveSeries) {
            $tag->active_media_videos_count = $totalActiveVideo;
            $tag->active_media_albums_count = $totalActiveAlbum;
            $tag->active_media_series_count = $totalActiveSeries;
            $tag->save();
        });
    }

}
