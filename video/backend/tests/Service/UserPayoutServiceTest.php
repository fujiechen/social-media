<?php

use App\Dtos\UserShareDto;
use App\Events\UserPayoutSavedEvent;
use App\Models\UserPayout;
use App\Models\UserReferral;
use App\Models\UserShare;
use App\Services\UserPayoutService;
use App\Services\UserService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UserPayoutServiceTest extends TestCase
{
    public function testGivenParentReferToCompleteNewUserPayout() {
        $parent = $this->createUser();

        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        $userShare = $userService->createUserShare(new UserShareDto([
            'userId' => $parent->id,
            'shareableType' => UserShare::TYPE_USER,
            'url' => $this->faker()->url,
        ]));

        $this->createUser($userShare->id);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()->where('user_id', '=', $parent->id)->first();

        $this->assertEquals(env('CURRENCY_POINTS'), $userPayout->currency_name);
        $this->assertEquals(10000, $userPayout->amount_cents);
        $this->assertEquals(UserPayout::TYPE_EARNING, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_COMPLETED, $userPayout->status);
    }

    public function testGivenParentToProcessReferralCommissionPayout() {
        $parent = $this->createUser();

        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        $userShare = $userService->createUserShare(new UserShareDto([
            'userId' => $parent->id,
            'shareableType' => UserShare::TYPE_USER,
            'url' => $this->faker()->url,
        ]));
        $user = $this->createUser($userShare->id);

        Event::fake([UserPayoutSavedEvent::class]);

        /**
         * @var UserPayoutService $userPayoutService
         */
        $userPayoutService = app(UserPayoutService::class);
        $comment = $this->faker()->text;

        $userPayoutService->processReferralCommissionPayout($user->id, 100, $comment, null);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()
            ->where('user_id', '=', $parent->id)
            ->where('type', '=', UserPayout::TYPE_COMMISSION)
            ->first();

        /**
         * each referral got $total / $level * $totalParentUsers,
         */
        $this->assertEquals(100, $userPayout->amount_cents);
        $this->assertEquals(env('CURRENCY_CASH'), $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_COMMISSION, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);
    }

    public function testGivenMultipleParentToProcessReferralCommissionPayout() {
        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);

        $parent3 = $this->createUser();
        $userShare3 = $userService->createUserShare(new UserShareDto([
            'userId' => $parent3->id,
            'shareableType' => UserShare::TYPE_USER,
            'url' => $this->faker()->url,
        ]));

        $parent2 = $this->createUser($userShare3->id);
        $userShare2 = $userService->createUserShare(new UserShareDto([
            'userId' => $parent2->id,
            'shareableType' => UserShare::TYPE_USER,
            'url' => $this->faker()->url,
        ]));

        $parent1 = $this->createUser($userShare2->id);
        $userShare1 = $userService->createUserShare(new UserShareDto([
            'userId' => $parent1->id,
            'shareableType' => UserShare::TYPE_USER,
            'url' => $this->faker()->url,
        ]));

        $user = $this->createUser($userShare1->id);

        //user has 3 parents
        $this->assertEquals(3, UserReferral::query()
            ->where('sub_user_id', '=', $user->id)->count());

        //parent3 has 3 children
        $this->assertEquals(3, UserReferral::query()
            ->where('user_id', '=', $parent3->id)->count());

        //parent2 has 2 children
        $this->assertEquals(2, UserReferral::query()
            ->where('user_id', '=', $parent2->id)->count());

        //parent1 has 1 children
        $this->assertEquals(1, UserReferral::query()
            ->where('user_id', '=', $parent1->id)->count());


        /**
         * Payout 100 to parent3, 2, 1
         * each referral got $total / $level * $totalParentUsers,
         *  - parent3 get 180 /  (3 * 3) = 180 / 9 = 20
         *  - parent2 get 180 / (2 * 3) = 180 / 6 = 30
         *  - parent1 get 180 / (1 * 3) = 180 / 3 = 60
         */


        /**
         * @var UserPayoutService $userPayoutService
         */
        $userPayoutService = app(UserPayoutService::class);
        $comment = $this->faker()->text;

        $userPayoutService->processReferralCommissionPayout($user->id, 180, $comment, null);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()
            ->where('user_id', '=', $parent3->id)
            ->where('type', '=', UserPayout::TYPE_COMMISSION)
            ->first();

        /**
         * each referral got $total / $level * $totalParentUsers,
         *  parent3 get 180 /  (3 * 3) = 180 / 9 = 20
         */
        $this->assertEquals(20, $userPayout->amount_cents);
        $this->assertEquals(env('CURRENCY_CASH'), $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_COMMISSION, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()
            ->where('user_id', '=', $parent2->id)
            ->where('type', '=', UserPayout::TYPE_COMMISSION)
            ->first();

        /**
         * each referral got $total / $level * $totalParentUsers,
         * parent2 get 180 / (2 * 3) = 180 / 6 = 30
         */
        $this->assertEquals(30, $userPayout->amount_cents);
        $this->assertEquals(env('CURRENCY_CASH'), $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_COMMISSION, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);

        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::query()
            ->where('user_id', '=', $parent1->id)
            ->where('type', '=', UserPayout::TYPE_COMMISSION)
            ->first();

        /**
         * each referral got $total / $level * $totalParentUsers,
         * parent1 get 180 / (1 * 3) = 180 / 3 = 60
         */
        $this->assertEquals(60, $userPayout->amount_cents);
        $this->assertEquals(env('CURRENCY_CASH'), $userPayout->currency_name);
        $this->assertEquals(UserPayout::TYPE_COMMISSION, $userPayout->type);
        $this->assertEquals(UserPayout::STATUS_PENDING, $userPayout->status);
    }
}
