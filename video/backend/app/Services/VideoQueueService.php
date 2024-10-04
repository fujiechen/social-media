<?php

namespace App\Services;

use App\Dtos\ResourceActorDto;
use App\Dtos\VideoDto;
use App\Dtos\VideoQueueDto;
use App\Dtos\VideoQueueSearchDto;
use App\Events\VideoQueueUpdatedEvent;
use App\Models\ResourceActor;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use App\Models\Video;
use App\Models\VideoQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class VideoQueueService
{
    private ResourceVideoService $resourceVideoService;
    private VideoService $videoService;

    public function __construct(ResourceVideoService $resourceVideoService, VideoService $videoService)
    {
        $this->resourceVideoService = $resourceVideoService;
        $this->videoService = $videoService;
    }

    public function updateOrCreateVideoQueue(VideoQueueDto $dto): VideoQueue
    {
        $foundVideoQueues = $this->fetchAllVideoQueueQuery(new VideoQueueSearchDto([
            'resourceId' => $dto->resourceId,
            'resourceVideoUrl' => $dto->resourceVideoUrl,
            'statuses' => [VideoQueue::STATUS_COMPLETED],
        ]));

        if ($foundVideoQueues->count() === 0) {
            $videoQueue = VideoQueue::create([
                'resource_id' => $dto->resourceId,
                'resource_video_url' => $dto->resourceVideoUrl,
                'playlist_queue_id' => $dto?->playlistQueueId,
                'prefill_json' => $dto?->prefillJson,
                'media_queue_id' => $dto?->mediaQueueId,
                'series_queue_id' => $dto->seriesQueueId,
            ]);
        } else {
            /**
             * @var VideoQueue $videoQueue
             */
            $videoQueue = $foundVideoQueues->first()->replicate();
            $videoQueue->media_queue_id = $dto->mediaQueueId;
            $videoQueue->series_queue_id = $dto->seriesQueueId;
            $videoQueue->playlist_queue_id = $dto->playlistQueueId;
            $videoQueue->save();
            Event::dispatch(new VideoQueueUpdatedEvent($videoQueue));
        }

        return $videoQueue;
    }

    public function fetchAllVideoQueueQuery(VideoQueueSearchDto $videoQueueSearchDto): Builder
    {
        $query = VideoQueue::query();

        if (!empty($videoQueueSearchDto->statuses)) {
            $query->whereIn('status', $videoQueueSearchDto->statuses);
        }

        if (!empty($videoQueueSearchDto->videoQueueIds)) {
            $query->whereIn('id', $videoQueueSearchDto->videoQueueIds);
        }

        if (!empty($videoQueueSearchDto->resourceId)) {
            $query->where('resource_id', $videoQueueSearchDto->resourceId);
        }

        if (!empty($videoQueueSearchDto->resourceVideoUrl)) {
            $query->where('resource_video_url', $videoQueueSearchDto->resourceVideoUrl);
        }

        return $query;
    }

    public function updateStatus(int $videoQueueId, string $status): VideoQueue
    {
        return DB::transaction(function () use ($videoQueueId, $status) {
            $videoQueue = VideoQueue::find($videoQueueId);
            $videoQueue->status = $status;
            $videoQueue->save();
            return $videoQueue;
        });
    }

    public function completeVideoQueue(VideoQueueDto $dto): ?VideoQueue
    {
        return DB::transaction(function () use ($dto) {
            /**
             * @var VideoQueue $videoQueue
             */
            $videoQueue = VideoQueue::find($dto->videoQueueId);

            if (empty($videoQueue)) {
                return null;
            }

            if ($videoQueue->status == VideoQueue::STATUS_COMPLETED) {
                return null;
            }

            $dto->resourceVideoDto->resourceId = $videoQueue->resource_id;
            $dto->resourceVideoDto->resourceVideoUrl = $videoQueue->resource_video_url;

            //set resource video dto from prefill if video queue has prefill values
            if (!empty($videoQueue->prefill_json)) {
                $dto->resourceVideoDto->name = $videoQueue->prefill_json['name'] ?? '';
                $dto->resourceVideoDto->metaJson = $videoQueue->prefill_json['metas'] ?? '';

                $actors = $videoQueue->prefill_json['actors'] ?? [];
                $resourceActorDtos = null;
                foreach ($actors as $resourceActor) {
                    $resourceActorDtos[] = new ResourceActorDto([
                        'name' => $resourceActor['name'],
                        'country' => $resourceActor['country'] ?? ''
                    ]);
                }
                $dto->resourceVideoDto->resourceActorDtos = $resourceActorDtos;
            }

            $resourceVideo = $this->resourceVideoService->updateOrCreateResourceVideo($dto->resourceVideoDto);

            //find matched tag id, actor id, category id
            $tagIds = array_filter(array_unique(ResourceTag::query()
                ->whereIn('name', array_column($dto->resourceVideoDto->resourceTagDtos, 'name'))
                ->where('resource_id', '=', $videoQueue->resource_id)
                ->pluck('tag_id')
                ->toArray()), function ($item) {
                    return $item > 0;
            });

            $actorIds = array_filter(array_unique(ResourceActor::query()
                ->whereIn('name', array_column($dto->resourceVideoDto->resourceActorDtos, 'name'))
                ->where('resource_id', '=', $videoQueue->resource_id)
                ->pluck('actor_id')
                ->toArray()), function ($item) {
                return $item > 0;
            });

            $categoryIds = array_filter(array_unique(ResourceCategory::query()
                ->whereIn('name', array_column($dto->resourceVideoDto->resourceCategoryDtos, 'name'))
                ->where('resource_id', '=', $videoQueue->resource_id)
                ->pluck('category_id')
                ->toArray()), function ($item) {
                return $item > 0;
            });

            //create video from resource video
            $video = $this->videoService->updateOrCreateVideo(new VideoDto([
                'videoId' => 0,
                'type' => Video::TYPE_RESOURCE,
                'resourceVideoId' => $resourceVideo->id,
                'tagIds' => $tagIds,
                'categoryIds' => $categoryIds,
                'actorIds' => $actorIds,
            ]));

            $videoQueue->resource_video_id = $resourceVideo->id;
            $videoQueue->video_id = $video->id;
            $videoQueue->status = VideoQueue::STATUS_COMPLETED;
            $videoQueue->response = $dto->toArray();
            $videoQueue->save();

            return $videoQueue;
        });
    }
}
