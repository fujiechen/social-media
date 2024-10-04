<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaSearch;

class MediaSearchService
{
    public function reBuildMediaSearchText(int $mediaId): void
    {
        MediaSearch::query()->where('media_id', '=', $mediaId)->delete();

        $searchText = [];

        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);
        $searchText[] = $media->user->nickname;
        $searchText[] = $media->name;
        $searchText[] = $media->description;

        foreach ($media->tags as $tag) {
            $searchText[] = $tag->name;
        }

        foreach ($media->actors as $actor) {
            $searchText[] = $actor->name;
        }

        foreach ($media->categories as $category) {
            $searchText[] = $category->name;
        }

        $searchText[] = $media->mediaable->name;
        $searchText[] = $media->mediaable->description;

        if ($media->isSeries()) {
            foreach ($media->mediaable->videos as $video) {
                $searchText[] = $video->name;
                $searchText[] = $video->description;
            }
        }

        $data = [];
        foreach ($searchText as $search) {
            if (!empty($search)) {
                $data[] = ['media_id' => $mediaId, 'search_text' => $search];
            }
        }

        MediaSearch::query()->insert($data);
    }
}
