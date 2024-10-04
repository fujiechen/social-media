<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MediaHistoryService
{
    public function createMediaHistory(int $userId, int $mediaId) {
        $today = Carbon::today();
        MediaHistory::query()
            ->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId)
            ->whereDate('created_at', $today)->delete();

        return MediaHistory::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
        ]);
    }

    public function findHistoryMediasQuery(int $userId): Builder
    {
        return Media::query()
            ->select('medias.*')
            ->distinct('medias.id')
            ->join('media_histories', 'medias.id', '=', 'media_histories.media_id')
            ->where('media_histories.user_id', '=', $userId)
            ->orderBy('media_histories.id', 'desc');
    }
}
