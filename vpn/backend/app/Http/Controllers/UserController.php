<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\Order;
use App\Models\Role;
use App\Models\UserPayout;
use App\Services\CategoryUserService;
use App\Services\OrderService;
use App\Services\ServerUserService;
use App\Services\UserPayoutService;
use App\Services\UserService;
use App\Transformers\OrderTransformer;
use App\Transformers\UserCategoryTransformer;
use App\Transformers\UserPayoutTransformer;
use App\Transformers\UserReferralTransformer;
use App\Transformers\UserServerTransformer;
use App\Transformers\UserTransformer;
use App\Utils\Utilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class UserController extends Controller
{
    private Fractal $fractal;
    private UserTransformer $userTransformer;
    private UserServerTransformer $userServerTransformer;
    private ServerUserService $serverUserService;
    private UserService $userService;
    private OrderService $orderService;
    private OrderTransformer $orderTransformer;
    private UserPayoutService $userPayoutService;
    private UserPayoutTransformer $userPayoutTransformer;
    private UserReferralTransformer $userReferralTransformer;
    private CategoryUserService $categoryUserService;
    private UserCategoryTransformer $userCategoryTransformer;

    public function __construct(Fractal               $fractal, UserService $userService,
                                ServerUserService     $serverUserService, UserTransformer $userTransformer,
                                UserServerTransformer $userServerTransformer, OrderService $orderService,
                                OrderTransformer      $orderTransformer, UserPayoutService $userPayoutService,
                                UserPayoutTransformer $userPayoutTransformer, UserReferralTransformer $userReferralTransformer,
                                CategoryUserService   $categoryUserService, UserCategoryTransformer $userCategoryTransformer)
    {
        $this->fractal = $fractal;
        $this->userService = $userService;
        $this->userTransformer = $userTransformer;
        $this->userServerTransformer = $userServerTransformer;
        $this->serverUserService = $serverUserService;
        $this->orderService = $orderService;
        $this->orderTransformer = $orderTransformer;
        $this->userPayoutService = $userPayoutService;
        $this->userPayoutTransformer = $userPayoutTransformer;
        $this->userReferralTransformer = $userReferralTransformer;
        $this->categoryUserService = $categoryUserService;
        $this->userCategoryTransformer = $userCategoryTransformer;
    }

    public function show(): JsonResponse
    {
        $resource = new Item(Auth::user(), $this->userTransformer);
        $this->fractal->parseIncludes(['email', 'roles']);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function servers(int $categoryId): JsonResponse
    {
        $serverUsers = $this->serverUserService->fetchServerUsersQuery(Auth::user()->id, $categoryId)->get();
        $resource = new Collection($serverUsers, $this->userServerTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function userCategory(Request $request): JsonResponse
    {
        $userId = Auth::user()->id;
        $categoryUser = $this->categoryUserService->findCategoryUserByIp($userId, $request->ip());
        $resource = new Item($categoryUser, $this->userCategoryTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
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

    public function sendResetPasswordEmail(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $this->userService->sendResetPasswordEmail($email);
        return response()->json([]);
    }

    public function otherUserPayouts(): JsonResponse {
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
