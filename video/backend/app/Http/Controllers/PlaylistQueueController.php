<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaylistQueue\SearchPlaylistQueueRequest;
use App\Models\PlaylistQueue;
use App\Services\PlaylistQueueService;
use App\Transformers\PlaylistQueueTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class PlaylistQueueController extends Controller
{
    private Fractal $fractal;
    private PlaylistQueueService $playlistQueueService;
    private PlaylistQueueTransformer $playlistQueueTransformer;

    public function __construct(Fractal $fractal, PlaylistQueueService $playlistQueueService, PlaylistQueueTransformer $playlistQueueTransformer) {
        $this->fractal = $fractal;
        $this->playlistQueueService = $playlistQueueService;
        $this->playlistQueueTransformer = $playlistQueueTransformer;
    }

    public function index(SearchPlaylistQueueRequest $request): JsonResponse {
        $playlistQueuesQuery = $this->playlistQueueService
            ->fetchAllPlaylistQueueQuery($request->toDto())
            ->paginate($request->integer('per_page', 10));
        $playlistQueues = $playlistQueuesQuery->items();

        foreach ($playlistQueues as $playlistQueue) {
            $this->playlistQueueService->updateStatus($playlistQueue->id, PlaylistQueue::STATUS_STARTED);
        }

        $resource = new Collection($playlistQueues, $this->playlistQueueTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($playlistQueuesQuery));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToStarted(int $id): JsonResponse
    {
        $playlistQueue = $this->playlistQueueService->updateStatus($id, PlaylistQueue::STATUS_STARTED);
        $resource = new Item($playlistQueue, $this->playlistQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToError(int $id): JsonResponse
    {
        $playlistQueue = $this->playlistQueueService->updateStatus($id, PlaylistQueue::STATUS_ERROR);
        $resource = new Item($playlistQueue, $this->playlistQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToCompleted(int $id): JsonResponse
    {
        $playlistQueue = $this->playlistQueueService->updateStatus($id, PlaylistQueue::STATUS_COMPLETED);
        $resource = new Item($playlistQueue, $this->playlistQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
