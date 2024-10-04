<?php

namespace App\Http\Controllers;

use App\Services\Cache\MediaCacheService;
use App\Services\MediaHistoryService;
use App\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class MediaHistoryController extends Controller
{
    private Fractal $fractal;
    private MediaHistoryService $mediaHistoryService;
    private MediaTransformer $mediaTransformer;
    private MediaCacheService $mediaCacheService;

    public function __construct(
        Fractal $fractal,
        MediaHistoryService $mediaHistoryService,
        MediaTransformer $mediaTransformer,
        MediaCacheService $mediaCacheService,
    ) {
        $this->fractal = $fractal;
        $this->mediaHistoryService = $mediaHistoryService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaCacheService = $mediaCacheService;
    }

    public function index(Request $request): JsonResponse {
        $medias = $this->mediaHistoryService->findHistoryMediasQuery($request->user()->id)
            ->paginate($request->integer('per_page', 10));

        $resource = new Collection($medias->items(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $request->user()->id);
        return response()->json($result);
    }

}
