<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\SearchOrderRequest;
use App\Services\OrderService;
use App\Transformers\OrderTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Resource\Collection;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Item;

class OrderController extends Controller
{
    private Fractal $fractal;
    private OrderService $orderService;
    private OrderTransformer $orderTransformer;

    public function __construct(Fractal $fractal, OrderService $orderService, OrderTransformer $orderTransformer) {
        $this->fractal = $fractal;
        $this->orderService = $orderService;
        $this->orderTransformer = $orderTransformer;
    }

    public function index(SearchOrderRequest $request): JsonResponse {
        $orders = $this->orderService->fetchAllOrders($request->toDto())
            ->orderBy('id','desc')
            ->paginate($request->input('per_page'));
        $resource = new Collection($orders->items(), $this->orderTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($orders));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function show(int $orderId): JsonResponse {
        $order = $this->orderService->fetchAllOrders(new OrderSearchDto([
            'orderId' => $orderId,
            'userId' => Auth::user()->id,
        ]))->first();
        $this->fractal->parseIncludes(['payments']);
        $resource = new Item($order, $this->orderTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function store(CreateOrderRequest $createOrderRequest): JsonResponse {
        $order = $this->orderService->updateOrCreateOrder($createOrderRequest->toDto());
        $resource = new Item($order, $this->orderTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
