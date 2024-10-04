<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaCategory;
use Illuminate\Support\Facades\DB;

class MediaCategoryService
{
    public function updateOrCreateMediaCategory(int $mediaId): void {
        DB::transaction(function() use ($mediaId) {
            /**
             * @var Media $media
             */
            $media = Media::find($mediaId);

            MediaCategory::query()->where('media_id', '=', $mediaId)->delete();

            if ($media->isVideo() || $media->isAlbum()) {
                foreach ($media->mediaable->categories as $category) {
                    MediaCategory::create([
                        'media_id' => $mediaId,
                        'category_id' => $category->id
                    ]);
                }
            } else if ($media->isSeries()) {
                foreach ($media->mediaable->videos as $video) {
                    foreach ($video->categories as $category) {
                        MediaCategory::updateOrCreate([
                            'media_id' => $mediaId,
                            'category_id' => $category->id
                        ], [
                            'media_id' => $mediaId,
                            'category_id' => $category->id
                        ]);
                    }
                }
            }
        });
    }
}
