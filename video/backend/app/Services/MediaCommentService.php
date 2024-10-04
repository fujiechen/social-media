<?php

namespace App\Services;

use App\Events\Media\SyncMediaCommentCountEvent;
use App\Exceptions\IllegalArgumentException;
use App\Models\Media;
use App\Models\MediaComment;
use Illuminate\Database\Eloquent\Builder;

class MediaCommentService
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService) {
        $this->mediaService = $mediaService;
    }

    public function getTotalMediaCount(int $mediaId): int {
        return MediaComment::query()->where('media_id', '=', $mediaId)->count();
    }

    public function findMediaCommentsQuery(int $mediaId, int $userId = null, int $mediaCommentId = null): Builder {
        $query = MediaComment::query()
            ->where('media_id', '=', $mediaId);

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        if ($mediaCommentId) {
            $query->where('id', '=', $mediaCommentId);
        }

        return $query;
    }

    public function createMediaComment(int $userId, int $mediaId, string $comment): MediaComment {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        $mediaComment = MediaComment::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
            'comment' => $comment,
        ]);

        event(new SyncMediaCommentCountEvent($media));

        return $mediaComment;
    }

    public function updateMediaComment(int $userId, int $mediaId, int $mediaCommentId, string $comment): ?MediaComment {
        if (!$this->mediaService->isMediaAvailableToUser($userId, $mediaId)) {
            throw new IllegalArgumentException('mediaComment.permission', 'User does not have media permission');
        }

        /**
         * @var MediaComment $mediaComment
         */
        $mediaComment = MediaComment::query()
            ->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId)
            ->where('id', '=', $mediaCommentId)
            ->first();

        if ($mediaComment) {
            $mediaComment->comment = $comment;
            $mediaComment->save();
            return $mediaComment;
        }

        throw new IllegalArgumentException('mediaComment', 'Not exists');
    }

    public function deleteMediaComment(int $userId, int $mediaId, int $mediaCommentId): void {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        /**
         * @var MediaComment $mediaComment
         */
        $mediaComment = MediaComment::query()
            ->where('user_id', '=', $userId)
            ->where('media_id', '=', $mediaId)
            ->where('id', '=', $mediaCommentId)
            ->first();

        $mediaComment?->delete();

        event(new SyncMediaCommentCountEvent($media));
    }
}
