<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Models\Order;
use App\Models\Role;
use App\Models\UserPayout;
use App\Services\Cache\UserCacheService;
use App\Services\OrderService;
use App\Services\UserPayoutService;
use App\Services\UserService;
use App\Transformers\OrderTransformer;
use App\Transformers\UserPayoutTransformer;
use App\Transformers\UserReferralTransformer;
use App\Utils\Utilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class UserController extends Controller
{
    private Fractal $fractal;
    private OrderService $orderService;
    private OrderTransformer $orderTransformer;
    private UserPayoutService $userPayoutService;
    private UserPayoutTransformer $userPayoutTransformer;
    private UserReferralTransformer $userReferralTransformer;
    private UserService $userService;
    private UserCacheService $userCacheService;

    public function __construct(Fractal                 $fractal,
                                UserService             $userService,
                                OrderService            $orderService,
                                OrderTransformer        $orderTransformer,
                                UserPayoutService       $userPayoutService,
                                UserPayoutTransformer   $userPayoutTransformer,
                                UserReferralTransformer $userReferralTransformer,
                                UserCacheService        $userCacheService)
    {
        $this->fractal = $fractal;
        $this->userService = $userService;
        $this->orderService = $orderService;
        $this->orderTransformer = $orderTransformer;
        $this->userPayoutService = $userPayoutService;
        $this->userPayoutTransformer = $userPayoutTransformer;
        $this->userReferralTransformer = $userReferralTransformer;
        $this->userCacheService = $userCacheService;
    }

    public function show(): JsonResponse
    {
        $result = $this->userCacheService->getOrCreateUserProfile(Auth::id());
        return response()->json($result);
    }

    public function children(Request $request): JsonResponse
    {
        $userChildren = $this->userService->findSubUsersQuery(Auth::user()->id, [Role::ROLE_USER_ID])
            ->paginate($request->input('per_page'));
        $resource = new Collection($userChildren->items(), $this->userReferralTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($userChildren));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function childrenCompletedOrders(Request $request): JsonResponse
    {
        $userChildrenOrders = $this->orderService->fetchAllOrders(new OrderSearchDto([
            'parentUserId' => Auth::user()->id,
            'status' => Order::STATUS_COMPLETED,
        ]))->paginate($request->input('per_page'));
        $resource = new Collection($userChildrenOrders->items(), $this->orderTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($userChildrenOrders));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function completedUserPayouts(Request $request): JsonResponse
    {
        $completedUserPayouts = $this->userPayoutService
            ->fetchAllUserPayoutsQuery(Auth::user()->id, UserPayout::STATUS_COMPLETED)
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page'));
        $resource = new Collection($completedUserPayouts->items(), $this->userPayoutTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($completedUserPayouts));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function otherUserPayouts(): JsonResponse
    {
        $data = [];
        for ($i = 0; $i < 10; $i++) {
            $data[] = [
                'id' => $i,
                'user_nickname' => chr(rand(97, 122)) . '*****',
                'amount_formatted' => Utilities::formatCurrency(env('CURRENCY_CASH'), rand(100, 900)),
            ];
        }

        return response()->json([
            'data' => $data
        ]);
    }
}
