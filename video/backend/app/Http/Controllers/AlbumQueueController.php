<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlbumQueue\CompleteAlbumQueueRequest;
use App\Http\Requests\AlbumQueue\CreateAlbumQueueRequest;
use App\Http\Requests\AlbumQueue\SearchAlbumQueueRequest;
use App\Models\AlbumQueue;
use App\Services\AlbumQueueService;
use App\Transformers\AlbumQueueTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class AlbumQueueController extends Controller
{
    private Fractal $fractal;
    private AlbumQueueService $albumQueueService;
    private AlbumQueueTransformer $albumQueueTransformer;

    public function __construct(Fractal $fractal, AlbumQueueService $albumQueueService, AlbumQueueTransformer $albumQueueTransformer) {
        $this->fractal = $fractal;
        $this->albumQueueService = $albumQueueService;
        $this->albumQueueTransformer = $albumQueueTransformer;
    }

    public function index(SearchAlbumQueueRequest $request): JsonResponse {
        $albumQueuesQuery = $this->albumQueueService
            ->fetchAllAlbumQueueQuery($request->toDto())
            ->paginate($request->integer('per_page', 10));
        $albumQueues = $albumQueuesQuery->items();

        foreach ($albumQueues as $albumQueue) {
            $this->albumQueueService->updateStatus($albumQueue->id, AlbumQueue::STATUS_STARTED);
        }

        $resource = new Collection($albumQueues, $this->albumQueueTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($albumQueuesQuery));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function store(CreateAlbumQueueRequest $request): JsonResponse {
        $albumQueue = $this->albumQueueService->updateOrCreateAlbumQueue($request->toDto());
        $resource = new Item($albumQueue, $this->albumQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToStarted(int $id): JsonResponse
    {
        $albumQueue = $this->albumQueueService->updateStatus($id, AlbumQueue::STATUS_STARTED);
        $resource = new Item($albumQueue, $this->albumQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToError(int $id): JsonResponse
    {
        $albumQueue = $this->albumQueueService->updateStatus($id, AlbumQueue::STATUS_ERROR);
        $resource = new Item($albumQueue, $this->albumQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToCompleted(CompleteAlbumQueueRequest $request): JsonResponse
    {
        $albumQueue = $this->albumQueueService->completeAlbumQueue($request->toDto());
        $resource = new Item($albumQueue, $this->albumQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
