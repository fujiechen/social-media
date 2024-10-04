<?php

namespace Tests;

use App\Models\Currency;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $setUpHasRunOnce = false;

    protected function setUp(): void {
        parent::setUp();
        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');

            DB::table('currencies')->insert([
                ['id' => 1, 'name' => Currency::CNY, 'symbol' => '¥', 'is_default' => true],
                ['id' => 2, 'name' => Currency::USD, 'symbol' => '$', 'is_default' => false],
            ]);

            DB::table('currencies')->insert([
                ['id' => 3, 'name' => Currency::COIN, 'symbol' => '¥', 'is_default' => false,
                    'purchase_enabled' => false, 'deposit_enabled' => false, 'withdraw_enabled' => false,
                    'exchange_enabled' => false, 'transfer_enabled' => false]
            ]);

            static::$setUpHasRunOnce = true;
        }

    }
    protected function faker(): Generator {
        return $this->app->make('Faker\Generator');
    }

    protected function createUser(): User {
        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        return $userService->create(null, null, $this->faker()->name,
            $this->faker()->email, $this->faker()->password, $this->faker()->userName,
            'en', [Role::ROLE_USER_ID]);
    }

}
