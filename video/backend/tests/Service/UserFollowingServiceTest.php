<?php

use App\Models\UserFollowing;
use App\Services\UserFollowingService;
use Tests\TestCase;

class UserFollowingServiceTest extends TestCase
{
    public function testGetFollowingUsersOfPublisherQuery() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($follower->id, $publisher->id, null);

        $fansQuery = $userFollowingService->getFollowingUsersOfPublisherQuery($publisher->id);

        $this->assertEquals(1, $fansQuery->count());

        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $follower->id)
            ->first();
        $this->assertEquals($publisher->id, $userFollowing->publisher_user_id);
        $this->assertEquals($follower->id, $userFollowing->following_user_id);
        $this->assertNull($userFollowing->valid_until_at);
    }

    public function testGetPublisherUsersOfFollowerQuery() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($follower->id, $publisher->id, null);

        $publisherQuery = $userFollowingService->getPublisherUsersOfFollowerQuery($follower->id);
        $this->assertEquals(1, $publisherQuery->count());

        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $follower->id)
            ->first();
        $this->assertEquals($publisher->id, $userFollowing->publisher_user_id);
        $this->assertEquals($follower->id, $userFollowing->following_user_id);
        $this->assertFalse($userFollowing->isExpired());
        $this->assertNull($userFollowing->valid_until_at);
    }

    public function testGetFriendsQuery() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($follower->id, $publisher->id, null);

        $publisherFriendsQuery = $userFollowingService->getFriendsQuery($publisher->id);
        $this->assertEquals(0, $publisherFriendsQuery->count());

        $followerFriendsQuery = $userFollowingService->getFriendsQuery($follower->id);
        $this->assertEquals(0, $followerFriendsQuery->count());

        $userFollowingService->addSubscription($publisher->id, $follower->id, null);
        $publisherFriendsQuery = $userFollowingService->getFriendsQuery($publisher->id);
        $followerFriendsQuery = $userFollowingService->getFriendsQuery($follower->id);
        $this->assertEquals(1, $publisherFriendsQuery->count());
        $this->assertEquals(1, $followerFriendsQuery->count());
    }

    public function testHasUserFollowedToPublisherUser() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($follower->id, $publisher->id, null);
        $this->assertTrue($userFollowingService->hasUserFollowedToPublisherUser($follower->id, $publisher->id));
        $this->assertFalse($userFollowingService->hasUserFollowedToPublisherUser($publisher->id, $follower->id));
    }

    public function testGetUserSubscriptionRedirect() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        $this->createSubscriptionProduct($publisher->id, 'CNY', 10);

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);


        //test product buy to subscribe
        $redirect = $userFollowingService->getUserSubscriptionRedirect($follower->id, $publisher->id);
        $this->assertEquals(UserFollowing::USER_SUBSCRIBER_REDIRECT_PRODUCT, $redirect);

        //manually add subscription
        $userFollowingService->addSubscription($follower->id, $publisher->id, 30);

        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $follower->id)
            ->first();

        $this->assertEquals(30, $userFollowing->valid_until_at_days);
        $this->assertFalse($userFollowing->isExpired());

        //delete subscription, then test redirect
        $userFollowingService->deleteSubscription($publisher->id, $follower->id);
        $redirect = $userFollowingService->getUserSubscriptionRedirect($follower->id, $publisher->id);
        $this->assertNull($redirect);
    }

    public function testRestoreUserFollowing() {
        $publisher = $this->createUser();
        $user = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($user->id, $publisher->id, 10);

        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $user->id)
            ->first();

        $this->assertEquals(10, $userFollowing->valid_until_at_days);

        $userFollowingService->deleteSubscription($publisher->id, $user->id);

        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $user->id)
            ->first();

        $this->assertNull($userFollowing);

        $userFollowingService->addSubscription($user->id, $publisher->id, null);
        /**
         * @var UserFollowing $userFollowing
         */
        $userFollowing = UserFollowing::query()
            ->where('publisher_user_id', $publisher->id)
            ->where('following_user_id', $user->id)
            ->first();

        $this->assertEquals(10, $userFollowing->valid_until_at_days);
    }
}
