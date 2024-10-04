<?php

namespace App\Http\Controllers;

use App\Services\Cache\MediaCacheService;
use App\Services\MediaCommentService;
use App\Services\MediaService;
use App\Transformers\MediaCommentTransformer;
use App\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class MediaCommentController extends Controller
{
    private Fractal $fractal;
    private MediaService $mediaService;
    private MediaCommentService $mediaCommentService;
    private MediaTransformer $mediaTransformer;
    private MediaCommentTransformer $mediaCommentTransformer;
    private MediaCacheService $mediaCacheService;

    public function __construct(Fractal $fractal,
                                MediaCommentService $mediaCommentService,
                                MediaService $mediaService,
                                MediaTransformer $mediaTransformer,
                                MediaCommentTransformer $mediaCommentTransformer,
                                MediaCacheService $mediaCacheService) {
        $this->fractal = $fractal;
        $this->mediaService = $mediaService;
        $this->mediaCommentService = $mediaCommentService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaCommentTransformer = $mediaCommentTransformer;
        $this->mediaCacheService = $mediaCacheService;
    }

    public function index(Request $request, int $mediaId): JsonResponse {
        $mediaComments = $this->mediaCommentService->findMediaCommentsQuery($mediaId)
            ->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 10));

        $resource = new Collection($mediaComments->items(), $this->mediaCommentTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($mediaComments));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function store(Request $request, int $mediaId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
            'comment' => 'required',
        ]);

        $mediaComment = $this->mediaCommentService->createMediaComment($request->user()->id,
            $mediaId, $validatedData['comment']);
        $resource = new Item($mediaComment, $this->mediaCommentTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function show(Request $request, int $mediaId, $mediaCommentId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
            'mediaCommentId' => $mediaCommentId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
            'mediaCommentId' => 'required|exists:media_comments,id',
        ]);

        $mediaComment = $this->mediaCommentService
            ->findMediaCommentsQuery($validatedData['mediaId'], $request->user()->id, $validatedData['mediaCommentId'])
            ->withTrashed()
            ->first();
        $resource = new Item($mediaComment, $this->mediaCommentTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }


    public function update(Request $request, int $mediaId, $mediaCommentId): JsonResponse
    {
        $request->merge([
            'mediaId' => $mediaId,
            'mediaCommentId' => $mediaCommentId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
            'mediaCommentId' => 'required|exists:media_comments,id',
            'comment' => 'required',
        ]);

        $mediaComment = $this->mediaCommentService->updateMediaComment($request->user()->id,
            $validatedData['mediaId'], $validatedData['mediaCommentId'], $validatedData['comment']);
        $resource = new Item($mediaComment, $this->mediaCommentTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function destroy(Request $request, int $mediaId, $mediaCommentId): Response
    {
        $request->merge([
            'mediaId' => $mediaId,
            'mediaCommentId' => $mediaCommentId,
        ]);

        $validatedData = $request->validate([
            'mediaId' => 'required|exists:medias,id',
            'mediaCommentId' => 'required|exists:media_comments,id',
        ]);


        $this->mediaCommentService->deleteMediaComment($request->user()->id, $validatedData['mediaId'], $validatedData['mediaCommentId']);
        return response()->noContent(ResponseAlias::HTTP_OK);
    }

    public function medias(Request $request): JsonResponse {
        $medias = $this->mediaService->fetchAllMediaWithUserCommentQuery($request->user()->id)
            ->paginate($request->integer('per_page', 10));

        $resource = new Collection($medias->items(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $request->user()->id);
        return response()->json($result);
    }
}
