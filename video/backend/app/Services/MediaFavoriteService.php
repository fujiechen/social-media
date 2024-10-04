<?php

namespace App\Services;

use App\Events\Media\SyncMediaFavoriteCountEvent;
use App\Models\Media;
use App\Models\MediaFavorite;
use Illuminate\Database\Eloquent\Builder;

class MediaFavoriteService
{
    public function getTotalMediaCount(int $mediaId): int {
        return MediaFavorite::query()->where('media_id', '=', $mediaId)->count();
    }

    public function findFavoriteMediasQuery(int $userId): Builder
    {
        return Media::query()
            ->select('medias.*')
            ->join('media_favorites', 'medias.id', '=', 'media_favorites.media_id')
            ->where('media_favorites.user_id', '=', $userId)
            ->orderBy('media_favorites.id', 'desc');
    }

    public function toggleMediaFavorite(int $userId, int $mediaId): ?MediaFavorite {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);
        $query = MediaFavorite::query()->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId);

        if (!empty($query->count())) {
            $query->delete();
            event(new SyncMediaFavoriteCountEvent($media));
            return null;
        }

        $mediaFavorite = MediaFavorite::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
        ]);
        event(new SyncMediaFavoriteCountEvent($media));
        return $mediaFavorite;
    }
}
