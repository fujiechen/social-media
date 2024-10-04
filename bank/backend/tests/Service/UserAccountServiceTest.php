<?php

use App\Models\Currency;
use App\Models\UserAccount;
use App\Models\UserOrder;
use App\Services\UserAccountService;
use Tests\TestCase;

class UserAccountServiceTest extends TestCase
{
    public function testIsUserAccountHasOrderTypeEnabled() {
        $user = $this->createUser();

        /**
         * @var UserAccountService $userAccountService
         */
        $userAccountService = app(UserAccountService::class);

        /**
         * @var UserAccount $userAccount
         */
        $userAccount = $userAccountService->getUserAccountQuery($user->id, Currency::CNY)->first();

        $this->assertTrue($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_DEPOSIT));
        $this->assertTrue($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_PURCHASE));
        $this->assertTrue($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_EXCHANGE));
        $this->assertTrue($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_TRANSFER));
        $this->assertTrue($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_WITHDRAW));


        /**
         * @var Currency $currency
         */
        $currency = Currency::query()->where('name', '=', Currency::COIN)->first();
        $currency->purchase_enabled = false;
        $currency->deposit_enabled = false;
        $currency->withdraw_enabled = false;
        $currency->exchange_enabled = false;
        $currency->transfer_enabled = false;
        $currency->save();

        /**
         * @var UserAccount $userAccount
         */
        $userAccount = $userAccountService->getUserAccountQuery($user->id, Currency::COIN)->first();

        $this->assertFalse($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_DEPOSIT));
        $this->assertFalse($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_PURCHASE));
        $this->assertFalse($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_EXCHANGE));
        $this->assertFalse($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_TRANSFER));
        $this->assertFalse($userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_WITHDRAW));

    }
}
