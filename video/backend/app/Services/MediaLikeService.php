<?php

namespace App\Services;

use App\Events\Media\SyncMediaLikeCountEvent;
use App\Models\Media;
use App\Models\MediaLike;
use Illuminate\Database\Eloquent\Builder;

class MediaLikeService
{
    public function getTotalMediaCount(int $mediaId): int {
        return MediaLike::query()->where('media_id', '=', $mediaId)->count();
    }

    public function findLikeMediasQuery(int $userId, string $likeType = 'like'): Builder
    {
        return Media::query()
            ->select('medias.*')
            ->distinct()
            ->join('media_likes', 'medias.id', '=', 'media_likes.media_id')
            ->where('media_likes.user_id', '=', $userId)
            ->where('media_likes.type', '=', $likeType)
            ->orderBy('media_likes.id', 'desc');
    }

    public function toggleMediaLike(int $userId, int $mediaId): ?MediaLike {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        $query = MediaLike::query()->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId);

        $item = $query->first();

        if ($item) {
            if ($item->type == MediaLike::TYPE_LIKE) {
                $item->delete();
                event(new SyncMediaLikeCountEvent($media));
                return null;
            } else {
                $item->delete();
            }
        }

        $mediaLike = MediaLike::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
            'type' => MediaLike::TYPE_LIKE
        ]);
        event(new SyncMediaLikeCountEvent($media));
        return $mediaLike;
    }

    public function toggleMediaDislike(int $userId, int $mediaId): ?MediaLike {
        $query = MediaLike::query()->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId);

        $item = $query->first();

        if ($item) {
            if ($item->type == MediaLike::TYPE_DISLIKE) {
                $item->delete();
                return null;
            } else {
                $item->delete();
            }
        }

        return MediaLike::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
            'type' => MediaLike::TYPE_DISLIKE
        ]);
    }
}
