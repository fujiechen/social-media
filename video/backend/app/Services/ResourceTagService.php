<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoTag;

class ResourceTagService
{
    /**
     * When Resource Tag is assigned a Tag, build all related videos tags
     *
     * @param int $resourceTagId
     * @param int|null $removedTagId
     * @return bool
     */
    public function buildVideoTagsFromResourceTags(int $resourceTagId, ?int $removedTagId): bool
    {
        //delete all tags from original tag id
        if ($removedTagId) {
            VideoTag::query()
                ->where('tag_id', '=', $removedTagId)
                ->delete();
        }

        $videos = Video::query()
            ->select('videos.*')
            ->join('resource_videos', 'resource_videos.id', '=', 'videos.resource_video_id')
            ->join('resource_video_tags', 'resource_video_tags.resource_video_id', '=', 'resource_videos.id')
            ->where('resource_video_tags.resource_tag_id', '=', $resourceTagId);

        $videos->chunk(100, function ($chunk) {
            /**
             * @var Video $video
             */
            foreach ($chunk as $video) {
                //delete all resource tag
                VideoTag::query()
                    ->leftJoin('resource_tags', 'resource_tags.tag_id', '=', 'video_tags.tag_id')
                    ->whereNotNull('resource_tags.tag_id')
                    ->where('video_tags.video_id', '=', $video->id)
                    ->delete();

                //create tags for this video
                foreach ($video->resourceVideo->resourceTags as $resourceTag) {
                    if (empty($resourceTag->tag_id)) {
                        continue;
                    }

                    VideoTag::query()->firstOrCreate([
                        'video_id' => $video->id,
                        'tag_id' => $resourceTag->tag_id
                    ], [
                        'video_id' => $video->id,
                        'tag_id' => $resourceTag->tag_id,
                    ]);
                }
            }
        });

        return true;
    }
}
