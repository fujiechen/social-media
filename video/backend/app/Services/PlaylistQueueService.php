<?php

namespace App\Services;

use App\Dtos\PlaylistQueueDto;
use App\Dtos\PlaylistQueueSearchDto;
use App\Models\PlaylistQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PlaylistQueueService
{
    public function updateOrCreatePlaylistQueue(PlaylistQueueDto $dto): PlaylistQueue
    {
        return PlaylistQueue::updateOrcreate([
            'resource_id' => $dto->resourceId,
            'resource_playlist_url' => $dto->resourcePlaylistUrl,
        ]);
    }

    public function fetchAllPlaylistQueueQuery(PlaylistQueueSearchDto $playlistQueueSearchDto): Builder
    {
        $query = PlaylistQueue::query();

        if (!empty($playlistQueueSearchDto->statuses)) {
            $query->whereIn('status', $playlistQueueSearchDto->statuses);
        }

        if (!empty($playlistQueueSearchDto->playlistQueueIds)) {
            $query->whereIn('id', $playlistQueueSearchDto->playlistQueueIds);
        }

        if (!empty($playlistQueueSearchDto->resourceId)) {
            $query->where('resource_id', $playlistQueueSearchDto->resourceId);
        }

        return $query;
    }

    public function updateStatus(int $playlistQueueId, string $status): PlaylistQueue
    {
        return DB::transaction(function () use ($playlistQueueId, $status) {
            $playlistQueue = PlaylistQueue::find($playlistQueueId);
            $playlistQueue->status = $status;
            $playlistQueue->save();
            return $playlistQueue;
        });
    }
}
