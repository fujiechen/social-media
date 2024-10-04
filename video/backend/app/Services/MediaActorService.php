<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaActor;
use Illuminate\Support\Facades\DB;

class MediaActorService
{
    public function updateOrCreateMediaActor(int $mediaId): void {
        DB::transaction(function() use ($mediaId) {
            /**
             * @var Media $media
             */
            $media = Media::find($mediaId);

            MediaActor::query()->where('media_id', '=', $mediaId)->delete();

            if ($media->isVideo() || $media->isAlbum()) {
                foreach ($media->mediaable->actors as $actor) {
                    MediaActor::create([
                        'media_id' => $mediaId,
                        'actor_id' => $actor->id
                    ]);
                }

            } else if ($media->isSeries()) {
                foreach ($media->mediaable->videos as $video) {
                    foreach ($video->actors as $actor) {
                        MediaActor::updateOrCreate([
                            'media_id' => $mediaId,
                            'actor_id' => $actor->id
                        ], [
                            'media_id' => $mediaId,
                            'actor_id' => $actor->id
                        ]);
                    }
                }
            }
        });
    }
}
