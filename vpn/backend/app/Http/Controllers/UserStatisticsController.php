<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Models\Category;
use App\Models\CategoryUser;
use App\Models\Role;
use App\Models\ServerUser;
use App\Models\User;
use App\Services\CategoryUserService;
use App\Services\OrderService;
use App\Services\ServerUserService;
use App\Services\UserPayoutService;
use App\Services\UserService;
use App\Utils\Utilities;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserStatisticsController extends Controller
{
    private UserService $userService;
    private UserPayoutService $userPayoutService;
    private ServerUserService $serverUserService;
    private OrderService $orderService;
    private CategoryUserService $categoryUserService;

    public function __construct(UserService $userService, UserPayoutService $userPayoutService,
                                ServerUserService $serverUserService, OrderService $orderService, CategoryUserService $categoryUserService)
    {
        $this->userService = $userService;
        $this->userPayoutService = $userPayoutService;
        $this->serverUserService = $serverUserService;
        $this->orderService = $orderService;
        $this->categoryUserService = $categoryUserService;
    }

    public function show(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $data = [];

        //show statistics for user profile only
        $childrenCount = $this->userService->findSubUsersQuery($user->id, [Role::ROLE_USER_ID])->count();
        $ordersCount = $this->orderService->fetchAllOrders(new OrderSearchDto([
            'parentUserId' => $user->id,
        ]))->count();
        $sharesCount = $this->userService->findUserSharesQuery($user->id)->count();

        $totalCashPayoutsAmountInCents = $this->userPayoutService->getTotalSuccessfulPayoutInCents($user->id, env('CURRENCY_CASH'));
        $totalPointsPayoutAmountInCents = $this->userPayoutService->getTotalSuccessfulPayoutInCents($user->id, env('CURRENCY_POINTS'));

        $data['data']['referrals'] = [
            'shares_count' => $sharesCount,
            'users_count' => $childrenCount,
            'orders_count' => $ordersCount,
            'total_cash' => Utilities::formatCurrency(env('CURRENCY_CASH'), $totalCashPayoutsAmountInCents),
            'total_points' => Utilities::formatCurrency(env('CURRENCY_POINTS'), $totalPointsPayoutAmountInCents),
        ];

        //server expiration days
        foreach (Category::all() as $category) {
            $result = [
                'id' => $category->id,
                'name' => $category->name,
                'valid_until_at_days' => '未开通',
            ];

            /**
             * @var CategoryUser $categoryUser
             */
            $categoryUser = $this->categoryUserService->findCategoryUser($category->id, $user->id);

            if ($categoryUser) {
                $result['valid_until_at_formatted'] = $categoryUser->valid_until_at_formatted;
                if ($categoryUser->valid_until_at_days > 0) {
                    $result['valid_until_at_days'] = ((string)$categoryUser->valid_until_at_days) . '天到期';
                } else {
                    $result['valid_until_at_days'] = '已过期';
                }
            }

            $data['data']['categories'][] = $result;
        }

        return response()->json($data);
    }

}
