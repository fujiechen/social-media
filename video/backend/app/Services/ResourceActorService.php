<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoActor;

class ResourceActorService
{
    /**
     * WHen Resource Actor is assigned an Actor, build all related videos actors
     *
     * @param int $resourceActorId
     * @param int|null $originalActorId
     * @return bool
     */
    public function buildVideoActorsFromResourceActors(int $resourceActorId, ?int $originalActorId): bool {
        //delete all actors from original actor id
        if ($originalActorId) {
            VideoActor::query()
                ->where('actor_id', '=', $originalActorId)
                ->delete();
        }

        $videos = Video::query()
            ->select('videos.*')
            ->join('resource_videos', 'resource_videos.id', '=', 'videos.resource_video_id')
            ->join('resource_video_actors', 'resource_video_actors.resource_video_id', '=', 'resource_videos.id')
            ->where('resource_video_actors.resource_actor_id', '=', $resourceActorId);

        $videos->chunk(100, function ($chunk) {
            /**
             * @var Video $video
             */
            foreach ($chunk as $video) {
                //delete all resource actor
                VideoActor::query()
                    ->leftJoin('resource_actors', 'resource_actors.actor_id', '=', 'video_actors.actor_id')
                    ->whereNotNull('resource_actors.actor_id')
                    ->where('video_actors.video_id', '=', $video->id)
                    ->delete();

                //create actors for this video
                foreach ($video->resourceVideo->resourceActors as $resourceActor) {
                    if (empty($resourceActor->actor_id)) {
                        continue;
                    }

                    VideoActor::query()->firstOrCreate([
                        'video_id' => $video->id,
                        'actor_id' => $resourceActor->actor_id,
                    ], [
                        'video_id' => $video->id,
                        'actor_id' => $resourceActor->actor_id,
                    ]);
                }
            }
        });

        return true;
    }
}
