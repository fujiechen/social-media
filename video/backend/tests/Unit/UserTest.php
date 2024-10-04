<?php

use App\Models\Role;
use App\Models\UserFollowing;
use App\Models\UserRole;
use App\Services\UserFollowingService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testHasRole() {
        $user = $this->createUser();
        $this->assertTrue($user->hasRole(Role::ROLE_VISITOR_ID));
        $this->assertTrue($user->hasRole(Role::ROLE_USER_ID));
        $this->assertFalse($user->hasRole(Role::ROLE_MEMBERSHIP_ID));

        $this->assertEquals([Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID], $user->role_ids);
        $this->assertEquals([Role::ROLE_VISITOR_NAME, Role::ROLE_USER_NAME], $user->role_names);
        $this->assertEquals(2, $user->userRoles->count());

        //add an expired membership
        $now = Carbon::now();
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => Role::ROLE_MEMBERSHIP_ID,
            'valid_until_at' => $now->subDays(9)
        ]);

        $user->load('userRoles');
        $this->assertEquals(3, $user->userRoles->count());
        $this->assertEquals(-10, $user->getRole(Role::ROLE_MEMBERSHIP_ID)->valid_until_at_days);

        $this->assertTrue($user->hasRole(Role::ROLE_VISITOR_ID));
        $this->assertTrue($user->hasRole(Role::ROLE_USER_ID));
        $this->assertFalse($user->hasRole(Role::ROLE_MEMBERSHIP_ID));

        $this->assertEquals([Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID], $user->role_ids);
        $this->assertEquals([Role::ROLE_VISITOR_NAME, Role::ROLE_USER_NAME], $user->role_names);
    }

    public function testUserFollowing() {
        $publisher = $this->createUser();
        $follower = $this->createUser();

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($follower->id, $publisher->id, null);

        $this->assertEquals(1, $publisher->followerUsers->count());
        $this->assertEquals(1, $follower->publisherUsers->count());

        //add expired following
        $now = Carbon::now();
        UserFollowing::query()->updateOrCreate([
            'publisher_user_id' => $publisher->id,
            'following_user_id' => $follower->id,
        ], [
            'publisher_user_id' => $publisher->id,
            'following_user_id' => $follower->id,
            'valid_until_at' => $now->subDays(9),
        ]);

        $this->assertEquals(0, $publisher->totalFollowerUsers());
        $this->assertEquals(0, $follower->totalSubscriptions());
    }
}
