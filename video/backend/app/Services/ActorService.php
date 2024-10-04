<?php

namespace App\Services;

use App\Dtos\ActorDto;
use App\Events\Actor\AddActorViewCountEvent;
use App\Events\ResourceActorAddActorEvent;
use App\Events\ResourceActorRemoveActorEvent;
use App\Models\Actor;
use App\Models\Album;
use App\Models\Media;
use App\Models\MediaActor;
use App\Models\ResourceActor;
use App\Models\Series;
use App\Models\Video;
use Illuminate\Support\Facades\DB;

class ActorService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function getActorAndIncreaseCount(int $actorId): Actor {
        /**
         * @var Actor $actor
         */
        $actor = Actor::find($actorId);

        event(new AddActorViewCountEvent($actor));

        return $actor;
    }

    public function updateOrCreateActor(ActorDto $dto): Actor {
        return DB::transaction(function() use ($dto) {
            $avatarFileId = null;
            if ($dto->type == Actor::TYPE_CLOUD) {
                $avatarFileId = $dto->avatarFileDto->fileId;
            } else if ($dto->type == Actor::TYPE_UPLOAD) {
                $avatarFileId = $this->fileService->getOrCreateFile($dto->avatarFileDto)->id;
            }

            $actor = Actor::query()->updateOrCreate([
                'id' => $dto->actorId,
            ],[
                'name' => $dto->name,
                'description' => $dto->description,
                'priority' => $dto->priority,
                'country' => $dto->country,
                'avatar_file_id' => $avatarFileId
            ]);

            foreach (ResourceActor::query()
                         ->where('actor_id', '=', $actor->id)
                         ->get() as $resourceActor) {
                $resourceActor->actor_id = null;
                $resourceActor->save();
                event(new ResourceActorRemoveActorEvent($resourceActor->id, $actor->id));
            }

            foreach ($dto->resourceActorIds as $resourceActorId) {
                /**
                 * @var ResourceActor $resourceActor
                 */
                $resourceActor = ResourceActor::query()->find($resourceActorId);
                $resourceActor->actor_id = $actor->id;
                $resourceActor->save();
                event(new ResourceActorAddActorEvent($resourceActor->id));
            }

            return $actor;
        });
    }

    public function syncActiveMediaCount(Actor $actor): void {
        $totalActiveVideo = MediaActor::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_actors.media_id')
            ->where('actor_id', '=', $actor->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Video::class)
            ->count();

        $totalActiveAlbum = MediaActor::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_actors.media_id')
            ->where('actor_id', '=', $actor->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Album::class)
            ->count();

        $totalActiveSeries = MediaActor::query()
            ->select('distinct medias.id')
            ->join('medias', 'medias.id', '=', 'media_actors.media_id')
            ->where('actor_id', '=', $actor->id)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('medias.mediaable_type', '=', Series::class)
            ->count();

        Actor::withoutEvents(function () use ($actor, $totalActiveVideo, $totalActiveAlbum, $totalActiveSeries) {
            $actor->active_media_videos_count = $totalActiveVideo;
            $actor->active_media_albums_count = $totalActiveAlbum;
            $actor->active_media_series_count = $totalActiveSeries;
            $actor->save();
        });
    }
}
