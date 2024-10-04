<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFollowing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class UserFollowingService
{
    private ProductService $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    public function getFriendsQuery(int $userId): Builder {
        return User::query()
            ->select('users.*')
            ->distinct()
            ->join('user_followings as following_users', 'following_users.following_user_id', '=', 'users.id')
            ->join('user_followings as publisher_users', 'publisher_users.publisher_user_id', '=', 'users.id')
            ->where('following_users.publisher_user_id', '=', $userId)
            ->where('publisher_users.following_user_id', '=', $userId);
    }

    /**
     * 粉丝用户
     * @param int $publisherUserId
     * @return Builder
     */
    public function getFollowingUsersOfPublisherQuery(int $publisherUserId): Builder
    {
        return User::query()
            ->select('users.*')
            ->join('user_followings', 'user_followings.following_user_id', '=', 'users.id')
            ->where('user_followings.publisher_user_id', '=', $publisherUserId)
            ->whereNull('user_followings.deleted_at');
    }

    /**
     * 关注的用户
     * @param int $followerUserId
     * @return Builder
     */
    public function getPublisherUsersOfFollowerQuery(int $followerUserId): Builder
    {
        return User::query()
            ->select('users.*')
            ->join('user_followings', 'user_followings.publisher_user_id', '=', 'users.id')
            ->where('user_followings.following_user_id', '=', $followerUserId)
            ->whereNull('user_followings.deleted_at');
    }

    public function getUserFollowing(int $followerUserId, int $publisherUserId): ?UserFollowing {
        return UserFollowing::where('publisher_user_id', '=', $publisherUserId)
            ->where('following_user_id', '=', $followerUserId)
            ->where(function ($query) {
                $query->whereNull('valid_until_at')
                    ->orWhere('valid_until_at', '>', Carbon::now());
            })
            ->first();
    }

    public function hasUserFollowedToPublisherUser(int $followerUserId, int $publisherUserId): bool {
        $userFollowing = $this->getUserFollowing($followerUserId, $publisherUserId);
        return !empty($userFollowing);
    }

    /**
     *
     * @param int|null $followerUserId
     * @param int $publisherUserId
     * @return string|null
     */
    public function getUserSubscriptionRedirect(?int $followerUserId, int $publisherUserId): ?string {
        if (empty($followerUserId)) {
            return UserFollowing::USER_SUBSCRIBER_REDIRECT_REGISTRATION;
        }

        if ($followerUserId === $publisherUserId) {
            return null;
        }

        $products = $this->productService->findSubscriptionProducts($publisherUserId)->get();

        if ($products->isEmpty()) {
            return null;
        }

        // check all with trashed and in the same condition
        $userSubscription = UserFollowing::withTrashed()
            ->where('following_user_id', $followerUserId)
            ->where('publisher_user_id', $publisherUserId)
            ->where(function ($query) {
                $query->whereNull('valid_until_at')
                    ->orWhere('valid_until_at', '>', Carbon::now());
            })
            ->first();

        if ($userSubscription) {
            return null;
        }

        return UserFollowing::USER_SUBSCRIBER_REDIRECT_PRODUCT;
    }

    /**
     * This method handles valid subscription, which already have all the subscription condition check
     *
     * @param int $followerUserId
     * @param int $publisherUserId
     * @param int|null $days
     * @return bool
     */
    public function addSubscription(int $followerUserId, int $publisherUserId, ?int $days): bool
    {
        if ($followerUserId !== $publisherUserId) {
            // check if the valid subscription is trashed
            $userSubscription = UserFollowing::withTrashed()
                ->where('following_user_id', $followerUserId)
                ->where('publisher_user_id', $publisherUserId)
                ->first();

            if ($userSubscription) {
                $userSubscription->restore();
            } else {
                $validUntilAt = null;
                if (!empty($days)) {
                    $now = Carbon::now();
                    $validUntilAt = $now->addDays($days);
                }

                UserFollowing::query()->updateOrCreate([
                    'publisher_user_id' => $publisherUserId,
                    'following_user_id' => $followerUserId,
                ], [
                    'publisher_user_id' => $publisherUserId,
                    'following_user_id' => $followerUserId,
                    'valid_until_at' => $validUntilAt,
                ]);
            }
        }

        return true;
    }

    public function deleteSubscription(int $publisherUserId, int $followerUserId): bool
    {
        UserFollowing::query()
            ->where('publisher_user_id', '=', $publisherUserId)
            ->where('following_user_id', '=', $followerUserId)
            ->delete();

        return true;
    }
}
