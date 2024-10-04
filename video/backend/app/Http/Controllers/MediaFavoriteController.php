<?php

namespace App\Http\Controllers;

use App\Services\Cache\MediaCacheService;
use App\Services\MediaFavoriteService;
use App\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class MediaFavoriteController extends Controller
{
    private Fractal $fractal;
    private MediaFavoriteService $mediaFavoriteService;
    private MediaTransformer $mediaTransformer;
    private MediaCacheService $mediaCacheService;

    public function __construct(
        Fractal $fractal,
        MediaFavoriteService $mediaFavoriteService,
        MediaTransformer $mediaTransformer,
        MediaCacheService $mediaCacheService,
    ) {
        $this->fractal = $fractal;
        $this->mediaFavoriteService = $mediaFavoriteService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaCacheService = $mediaCacheService;
    }

    public function index(Request $request): JsonResponse {
        $medias = $this->mediaFavoriteService->findFavoriteMediasQuery($request->user()->id)
            ->paginate($request->integer('per_page', 10));

        $resource = new Collection($medias->items(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $request->user()->id);
        return response()->json($result);
    }

    public function toggle(Request $request, int $mediaId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
        ]);

        $mediaFavorite = $this->mediaFavoriteService->toggleMediaFavorite($request->user()->id, $validatedData['mediaId']);

        return response()->json([
            'favorite' => !is_null($mediaFavorite)
        ]);
    }
}
