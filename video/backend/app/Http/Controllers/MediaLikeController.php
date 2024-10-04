<?php

namespace App\Http\Controllers;

use App\Services\Cache\MediaCacheService;
use App\Services\MediaLikeService;
use App\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class MediaLikeController extends Controller
{
    private Fractal $fractal;
    private MediaLikeService $mediaLikeService;
    private MediaTransformer $mediaTransformer;
    private MediaCacheService $mediaCacheService;

    public function __construct(
        Fractal $fractal,
        MediaLikeService $mediaLikeService,
        MediaTransformer $mediaTransformer,
        MediaCacheService $mediaCacheService,
    ) {
        $this->fractal = $fractal;
        $this->mediaLikeService = $mediaLikeService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaCacheService = $mediaCacheService;
    }

    public function index(Request $request): JsonResponse {
        $medias = $this->mediaLikeService->findLikeMediasQuery($request->user()->id)
            ->paginate($request->integer('per_page', 10));

        $resource = new Collection($medias->items(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $request->user()->id);
        return response()->json($result);
    }

    public function toggleLike(Request $request, int $mediaId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
        ]);

        $mediaLike = $this->mediaLikeService->toggleMediaLike($request->user()->id, $validatedData['mediaId']);
        return response()->json([
            'like' => !is_null($mediaLike)
        ]);
    }

    public function toggleDislike(Request $request, int $mediaId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
        ]);

        $mediaDislike = $this->mediaLikeService->toggleMediaDislike($request->user()->id, $validatedData['mediaId']);

        return response()->json([
            'dislike' => !is_null($mediaDislike)
        ]);
    }
}
