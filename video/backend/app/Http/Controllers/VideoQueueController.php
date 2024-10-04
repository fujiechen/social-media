<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoQueue\CompleteVideoQueueRequest;
use App\Http\Requests\VideoQueue\CreateVideoQueueRequest;
use App\Http\Requests\VideoQueue\SearchVideoQueueRequest;
use App\Models\VideoQueue;
use App\Services\VideoQueueService;
use App\Transformers\VideoQueueTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class VideoQueueController extends Controller
{
    private Fractal $fractal;
    private VideoQueueService $videoQueueService;
    private VideoQueueTransformer $videoQueueTransformer;

    public function __construct(Fractal $fractal, VideoQueueService $videoQueueService, VideoQueueTransformer $videoQueueTransformer) {
        $this->fractal = $fractal;
        $this->videoQueueService = $videoQueueService;
        $this->videoQueueTransformer = $videoQueueTransformer;
    }

    public function index(SearchVideoQueueRequest $request): JsonResponse {
        $videoQueuesQuery = $this->videoQueueService
            ->fetchAllVideoQueueQuery($request->toDto())
            ->paginate($request->integer('per_page', 10));
        $videoQueues = $videoQueuesQuery->items();

        //update video queue items to started
        foreach ($videoQueues as $videoQueue) {
            $this->videoQueueService->updateStatus($videoQueue->id, VideoQueue::STATUS_STARTED);
        }

        $resource = new Collection($videoQueues, $this->videoQueueTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($videoQueuesQuery));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function store(CreateVideoQueueRequest $request): JsonResponse {
        $videoQueue = $this->videoQueueService->updateOrCreateVideoQueue($request->toDto());
        $resource = new Item($videoQueue, $this->videoQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToStarted(int $id): JsonResponse
    {
        $videoQueue = $this->videoQueueService->updateStatus($id, VideoQueue::STATUS_STARTED);
        $resource = new Item($videoQueue, $this->videoQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToError(int $id): JsonResponse
    {
        $videoQueue = $this->videoQueueService->updateStatus($id, VideoQueue::STATUS_ERROR);
        $resource = new Item($videoQueue, $this->videoQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function updateStatusToCompleted(CompleteVideoQueueRequest $request): JsonResponse
    {
        $videoQueue = $this->videoQueueService->completeVideoQueue($request->toDto());
        $resource = new Item($videoQueue, $this->videoQueueTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
