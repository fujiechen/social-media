<?php

namespace App\Services\Cache;

use App\Dtos\MediaSearchDto;
use App\Models\Media;
use App\Services\MediaRecommendationService;
use App\Services\MediaService;
use App\Transformers\MediaMetaTransformer;
use App\Transformers\MediaTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class MediaCacheService
{
    const PREFIX = 'media_';
    private Fractal $fractal;
    private MediaMetaTransformer $mediaMetaTransformer;
    private MediaTransformer $mediaTransformer;
    private MediaService $mediaService;

    public function __construct(Fractal $fractal, MediaTransformer $mediaTransformer,
                                MediaMetaTransformer $mediaMetaTransformer,
                                MediaService $mediaService)
    {
        $this->fractal = $fractal;
        $this->mediaMetaTransformer = $mediaMetaTransformer;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaService = $mediaService;
    }

    public function getOrCreateMediaSearchList(MediaSearchDto $dto, int $perPage, int $page): array
    {
        $key = self::PREFIX . 'search_' . md5(serialize($dto)) . "per_page_{$perPage}_page_{$page}";
        $mediasArray = Cache::remember($key, 3600, function() use ($dto, $perPage, $page){
            $medias = $this->mediaService->fetchAllMediasQuery($dto)
                ->paginate($perPage);
            $resource = new Collection($medias->getCollection(), $this->mediaTransformer);
            $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
            return $this->fractal->createData($resource)->toArray();
        });

        return $this->appendListMediaMeta($mediasArray, $dto->userId);
    }

    /**
     * PreloadCache
     * @param int $mediaId
     * @param int $limit
     * @return array
     */
    public function getOrCreateMediaSimilarList(int $mediaId, int $limit = 10, ?int $userId = null): array {
        $key = self::PREFIX . $mediaId . '_similar_' . $limit;
        $mediasArray = Cache::remember($key, 3600, function() use ($mediaId, $limit) {
            $medias = $this->mediaService->fetchSimilarMediasByVideoMedia($mediaId, $limit);
            $resource = new Collection($medias, $this->mediaTransformer);
            return $this->fractal->createData($resource)->toArray();
        });

        return $this->appendListMediaMeta($mediasArray, $userId);
    }

    /**
     * PreloadCache
     * @param Media $media
     * @param array $includes
     * @return array
     */
    public function getOrCreateMediaShow(Media $media, array $includes): array {
        $mediaId = $media->id;
        $key = self::PREFIX . $mediaId . '_includes_' . implode('_', $includes);
        return Cache::remember($key, 3600, function() use ($media, $includes) {
            $resource = new Item($media, $this->mediaTransformer);
            $this->fractal->parseIncludes($includes);
            return $this->fractal->createData($resource)->toArray();
        });
    }

    public function appendListMediaMeta(array $mediasArray, ?int $userId = null): array {
        $mediaIds = collect($mediasArray['data'])->pluck('id')->toArray();

        $mediaMetas = $this->mediaService->fetchAllMediasMetaQuery($mediaIds, $userId)->get();
        $resource = new Collection($mediaMetas, $this->mediaMetaTransformer);
        $mediaMetasArray = $this->fractal->createData($resource)->toArray();
        $mediasArray['data'] = array_merge_recursive_distinct($mediasArray['data'], $mediaMetasArray['data']);

        return $mediasArray;
    }

}
