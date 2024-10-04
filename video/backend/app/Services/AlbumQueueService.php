<?php

namespace App\Services;

use App\Dtos\AlbumDto;
use App\Dtos\AlbumQueueDto;
use App\Dtos\AlbumQueueSearchDto;
use App\Events\AlbumQueueUpdatedEvent;
use App\Models\Album;
use App\Models\AlbumQueue;
use App\Models\ResourceActor;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class AlbumQueueService
{
    private ResourceAlbumService $resourceAlbumService;
    private AlbumService $albumService;

    public function __construct(ResourceAlbumService $resourceAlbumService, AlbumService $albumService)
    {
        $this->resourceAlbumService = $resourceAlbumService;
        $this->albumService = $albumService;
    }

    public function updateOrCreateAlbumQueue(AlbumQueueDto $dto): AlbumQueue
    {
        $foundAlbumQueues = $this->fetchAllAlbumQueueQuery(new AlbumQueueSearchDto([
            'resourceId' => $dto->resourceId,
            'resourceVideoUrl' => $dto->resourceAlbumUrl,
            'statuses' => [AlbumQueue::STATUS_COMPLETED],
        ]));

        if ($foundAlbumQueues->count()) {
            $albumQueue = AlbumQueue::updateOrcreate([
                'resource_id' => $dto->resourceId,
                'resource_album_url' => $dto->resourceAlbumUrl,
                'playlist_queue_id' => $dto?->playlistQueueId,
                'media_queue_id' => $dto?->mediaQueueId,
                'series_queue_id' => $dto->seriesQueueId,
            ]);
        } else {
            /**
             * @var AlbumQueue $albumQueue
             */
            $albumQueue = $foundAlbumQueues->first()->replicate();
            $albumQueue->media_queue_id = $dto->mediaQueueId;
            $albumQueue->series_queue_id = $dto->seriesQueueId;
            $albumQueue->playlist_queue_id = $dto->playlistQueueId;
            $albumQueue->save();
            Event::dispatch(new AlbumQueueUpdatedEvent($albumQueue));
        }

        return $albumQueue;
    }

    public function fetchAllAlbumQueueQuery(AlbumQueueSearchDto $albumQueueSearchDto): Builder
    {
        $query = AlbumQueue::query();

        if (!empty($albumQueueSearchDto->statuses)) {
            $query->whereIn('status', $albumQueueSearchDto->statuses);
        }

        if (!empty($albumQueueSearchDto->albumQueueIds)) {
            $query->whereIn('id', $albumQueueSearchDto->albumQueueIds);
        }

        if (!empty($albumQueueSearchDto->resourceId)) {
            $query->where('resource_id', $albumQueueSearchDto->resourceId);
        }

        if (!empty($albumQueueSearchDto->resourceAlbumUrl)) {
            $query->where('resource_album_url', $albumQueueSearchDto->resourceAlbumUrl);
        }

        return $query;
    }

    public function updateStatus(int $albumQueueId, string $status): AlbumQueue
    {
        return DB::transaction(function () use ($albumQueueId, $status) {
            $albumQueue = AlbumQueue::find($albumQueueId);
            $albumQueue->status = $status;
            $albumQueue->save();
            return $albumQueue;
        });
    }

    public function completeAlbumQueue(AlbumQueueDto $dto): ?AlbumQueue
    {
        return DB::transaction(function () use ($dto) {

            /**
             * @var AlbumQueue $albumQueue
             */
            $albumQueue = AlbumQueue::find($dto->albumQueueId);

            if (empty($albumQueue)) {
                return null;
            }

            if ($albumQueue->status == AlbumQueue::STATUS_COMPLETED) {
                return null;
            }

            $dto->resourceAlbumDto->resourceId = $albumQueue->resource_id;
            $dto->resourceAlbumDto->resourceAlbumUrl = $albumQueue->resource_album_url;

            $resourceAlbum = $this->resourceAlbumService->updateOrCreateResourceAlbum($dto->resourceAlbumDto);

            //find matched tag id, actor id, category id
            $tagIds = array_filter(array_unique(ResourceTag::query()
                ->whereIn('name', array_column($dto->resourceAlbumDto->resourceTagDtos, 'name'))
                ->where('resource_id', '=', $albumQueue->resource_id)
                ->pluck('tag_id')
                ->toArray()), function ($item) {
                    return $item > 0;
            });

            $actorIds = array_filter(array_unique(ResourceActor::query()
                ->whereIn('name', array_column($dto->resourceAlbumDto->resourceActorDtos, 'name'))
                ->where('resource_id', '=', $albumQueue->resource_id)
                ->pluck('actor_id')
                ->toArray()), function ($item) {
                return $item > 0;
            });

            $categoryIds = array_filter(array_unique(ResourceCategory::query()
                ->whereIn('name', array_column($dto->resourceAlbumDto->resourceCategoryDtos, 'name'))
                ->where('resource_id', '=', $albumQueue->resource_id)
                ->pluck('category_id')
                ->toArray()), function ($item) {
                return $item > 0;
            });

            $album = $this->albumService->updateOrCreateAlbum(new AlbumDto([
                'albumId' => 0,
                'type' => Album::TYPE_RESOURCE,
                'resourceAlbumId' => $resourceAlbum->id,
                'tagIds' => $tagIds,
                'categoryIds' => $categoryIds,
                'actorIds' => $actorIds,
            ]));

            $albumQueue->resource_album_id = $resourceAlbum->id;
            $albumQueue->album_id = $album->id;
            $albumQueue->status = AlbumQueue::STATUS_COMPLETED;
            $albumQueue->response = $dto->toArray();
            $albumQueue->save();

            return $albumQueue;
        });
    }
}
