<?php

namespace App\Http\Controllers;

use App\Dtos\OrderSearchDto;
use App\Models\MediaLike;
use App\Models\Role;
use App\Models\User;
use App\Services\MediaFavoriteService;
use App\Services\MediaLikeService;
use App\Services\OrderService;
use App\Services\UserPayoutService;
use App\Services\UserService;
use App\Utils\Utilities;
use Illuminate\Http\JsonResponse;

class UserStatisticsController extends Controller
{
    private UserService $userService;
    private MediaLikeService $mediaLikeService;
    private MediaFavoriteService $mediaFavoriteService;
    private UserPayoutService $userPayoutService;
    private OrderService $orderService;

    public function __construct(UserService             $userService,
                                MediaLikeService        $mediaLikeService,
                                MediaFavoriteService $mediaFavoriteService,
                                UserPayoutService $userPayoutService,
                                OrderService $orderService)
    {
        $this->userService = $userService;
        $this->mediaLikeService = $mediaLikeService;
        $this->mediaFavoriteService = $mediaFavoriteService;
        $this->userPayoutService = $userPayoutService;
        $this->orderService = $orderService;
    }

    public function show(): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = auth('api')->user();
        $subscribersCount = $user->totalFollowerUsers();
        $subscriptionCount = $user->totalSubscriptions();
        $userId = $user->id;

        $likeCount = $this->mediaLikeService->findLikeMediasQuery($userId, MediaLike::TYPE_LIKE)->count();
        $dislikesCount = $this->mediaLikeService->findLikeMediasQuery($userId, MediaLike::TYPE_DISLIKE)->count();
        $favoriteCount = $this->mediaFavoriteService->findFavoriteMediasQuery($userId)->count();

        $seriesCount = $user->totalMediaSeries();
        $videosCount = $user->totalMediaVideos();
        $albumsCount = $user->totalMediaAlbums();

        $data = [
            'data' => [
                'publisher' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'subscriptions_count' => $subscriptionCount,
                    'subscribers_count' => $subscribersCount,
                ],
                'medias' => [
                    'medias_count' => $seriesCount + $videosCount + $albumsCount,
                    'series_count' => $seriesCount,
                    'videos_count' => $videosCount,
                    'albums_count' => $albumsCount,
                    'likes_count' => $likeCount,
                    'dislikes_count' => $dislikesCount,
                    'favorites_count' => $favoriteCount,
                ],
            ]
        ];

        $childrenCount = $this->userService->findSubUsersQuery($user->id)->count();
        $ordersCount = $this->orderService->fetchAllOrders(new OrderSearchDto([
            'parentUserId' => $user->id,
        ]))->count();
        $sharesCount = $this->userService->findUserSharesQuery($user->id)->count();

        $totalCashPayoutsAmount = $this->userPayoutService->getTotalSuccessfulPayout($userId, env('CURRENCY_CASH'));
        $totalPointsPayoutAmount = $this->userPayoutService->getTotalSuccessfulPayout($userId, env('CURRENCY_POINTS'));

        $data['data']['referrals'] = [
            'shares_count' => $sharesCount,
            'users_count' => $childrenCount,
            'orders_count' => $ordersCount,
            'total_cash' => Utilities::formatCurrency(env('CURRENCY_CASH'), $totalCashPayoutsAmount * 100),
            'total_points' => $totalPointsPayoutAmount,
        ];

        return response()->json($data);
    }

}
