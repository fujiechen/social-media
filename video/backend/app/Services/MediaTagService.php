<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaTag;
use Illuminate\Support\Facades\DB;

class MediaTagService
{
    public function updateOrCreateMediaTag(int $mediaId): void {
        DB::transaction(function() use ($mediaId) {
            /**
             * @var Media $media
             */
            $media = Media::find($mediaId);

            MediaTag::query()->where('media_id', '=', $mediaId)->delete();

            if ($media->isVideo() || $media->isAlbum()) {
                foreach ($media->mediaable->tags as $tag) {
                    MediaTag::create([
                        'media_id' => $mediaId,
                        'tag_id' => $tag->id
                    ]);
                }
            } else if ($media->isSeries()) {
                foreach ($media->mediaable->videos as $video) {
                    foreach ($video->tags as $tag) {
                        MediaTag::updateOrCreate([
                            'media_id' => $mediaId,
                            'tag_id' => $tag->id
                        ], [
                            'media_id' => $mediaId,
                            'tag_id' => $tag->id
                        ]);
                    }
                }
            }
        });
    }
}
