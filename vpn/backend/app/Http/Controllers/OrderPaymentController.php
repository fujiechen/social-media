<?php

namespace App\Http\Controllers;

use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\Payment\CreateOrderPaymentRequest;
use App\Http\Requests\Payment\GetUserBalanceRequest;
use App\Services\PaymentService;
use App\Transformers\OrderPaymentTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Item;

class OrderPaymentController extends Controller
{
    private PaymentService $paymentService;
    private Fractal $fractal;
    private OrderPaymentTransformer $orderPaymentTransformer;

    public function __construct(Fractal $fractal, OrderPaymentTransformer $orderPaymentTransformer, PaymentService $paymentService) {
        $this->paymentService = $paymentService;
        $this->fractal = $fractal;
        $this->orderPaymentTransformer = $orderPaymentTransformer;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function balance(GetUserBalanceRequest $request): JsonResponse {
        $userAccountDtos = $this->paymentService->getUserAccount(Auth::id(), $request->input('currency_name'));
        return response()->json(['data' => $userAccountDtos->toArray()]);
    }

    public function store(CreateOrderPaymentRequest $request): JsonResponse {
        $orderPayment = $this->paymentService->createOrderPayment($request->input('order_id'));
        $resource = new Item($orderPayment, $this->orderPaymentTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }


}
