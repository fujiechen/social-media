<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\SearchOrderRequest;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Transformers\OrderPaymentTransformer;
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
    private PaymentService $paymentService;
    private OrderPaymentTransformer $orderPaymentTransformer;

    public function __construct(
        Fractal $fractal,
        OrderService $orderService,
        OrderTransformer $orderTransformer,
        PaymentService $paymentService,
        OrderPaymentTransformer $orderPaymentTransformer
    ) {
        $this->fractal = $fractal;
        $this->orderService = $orderService;
        $this->orderTransformer = $orderTransformer;
        $this->paymentService = $paymentService;
        $this->orderPaymentTransformer = $orderPaymentTransformer;
    }

    public function index(SearchOrderRequest $request): JsonResponse {
        $orders = $this->orderService->fetchAllOrders($request->toDto())->orderByDesc('orders.id')->paginate($request->input('per_page'));
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

    /**
     * @throws IllegalArgumentException
     */
    public function store(CreateOrderRequest $createOrderRequest): JsonResponse {
        $order = $this->orderService->updateOrCreateOrder($createOrderRequest->toDto());
        $resource = new Item($order, $this->orderTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    /**
     * @throws IllegalArgumentException
     */
    public function instantPayment(CreateOrderRequest $createOrderRequest): JsonResponse {
        $order = $this->orderService->updateOrCreateOrder($createOrderRequest->toDto());
        $orderPayment = $this->paymentService->createOrderPayment($order->id);
        $resource = new Item($orderPayment, $this->orderPaymentTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
