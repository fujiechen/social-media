<?php

use App\Models\Currency;
use App\Models\CurrencyRate;
use App\Models\UserAccount;
use App\Models\UserOrder;
use App\Models\UserTransaction;
use App\Services\UserAccountService;
use App\Services\UserOrderService;
use Tests\TestCase;

class UserOrderServiceTest extends TestCase
{
    public function testCreateDepositOrderAsFailedThenSuccessful() {
        $user = $this->createUser();

        /**
         * @var UserAccountService $userAccountService
         */
        $userAccountService = app(UserAccountService::class);

        /**
         * @var UserAccount $userAccount
         */
        $userAccount = $userAccountService->getUserAccountQuery($user->id, Currency::CNY)->first();
        $this->assertEquals(0, $userAccount->balance);

        /**
         * @var UserOrderService $userOrderService
         */
        $userOrderService = app(UserOrderService::class);
        $userOrder = $userOrderService->createDepositOrder($userAccount->id, 10, 'deposit comment');

        $this->assertEquals(1000, $userOrder->amount);
        $this->assertEquals(UserOrder::TYPE_DEPOSIT, $userOrder->type);
        $this->assertEquals(UserOrder::STATUS_PENDING, $userOrder->status);

        //failed transaction
        $userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_FAILED, $userOrder->id);

        $userAccount->refresh();
        $this->assertEquals(0, $userAccount->balance);

        $userOrder->refresh();
        $this->assertEquals(1, $userOrder->userTransactions()->count());
        $this->assertEquals(0, $userOrder->userOrderPayments()->count());

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = $userOrder->userTransactions()->first();
        $this->assertEquals(1000, $userTransaction->amount);
        $this->assertEquals(0, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_FAILED, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_INCOME, $userTransaction->type);

        //successful transaction
        $userOrderService->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_SUCCESSFUL, $userOrder->id);

        $userAccount->refresh();
        $this->assertEquals(1000, $userAccount->balance);

        $userOrder->refresh();
        $this->assertEquals(2, $userOrder->userTransactions()->count());
        $this->assertEquals(0, $userOrder->userOrderPayments()->count());

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = $userOrder->userTransactions()->orderBy('id', 'desc')->first();
        $this->assertEquals(1000, $userTransaction->amount);
        $this->assertEquals(1000, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_SUCCESSFUL, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_INCOME, $userTransaction->type);
    }

    public function testCreateExchangeOrderFailedThenSuccessful() {
        /**
         * @var UserAccountService $userAccountService
         */
        $userAccountService = app(UserAccountService::class);

        $user = $this->createUser();

        /**
         * @var UserAccount $userAccountCNY
         */
        $userAccountCNY = $userAccountService->getUserAccountQuery($user->id, Currency::CNY)->first();

        /**
         * @var UserAccount $userAccountUSD
         */
        $userAccountUSD = $userAccountService->getUserAccountQuery($user->id, Currency::USD)->first();
        $userAccountUSD->balance = 100; // $1 dollar
        $userAccountUSD->save();


        //create exchange rate
        $cny = Currency::where('name', '=', Currency::CNY)->first();
        $usd = Currency::where('name', '=', Currency::USD)->first();

        CurrencyRate::create([
            'from_currency_id' => $usd->id,
            'to_currency_id' => $cny->id,
            'rate' => 7.38
        ]);

        /**
         * @var UserOrderService $userOrderService
         */
        $userOrderService = app(UserOrderService::class);

        //not enough money
        try {
            $userOrderService->createExchangeOrder($userAccountUSD->id, $userAccountCNY->id, 100);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $userOrder = $userOrderService->createExchangeOrder($userAccountUSD->id, $userAccountCNY->id, 1);

        $this->assertEquals(100, $userOrder->amount);
        $this->assertEquals(UserOrder::TYPE_EXCHANGE, $userOrder->type);
        $this->assertEquals(UserOrder::STATUS_SUCCESSFUL, $userOrder->status);
        $this->assertEquals($userAccountCNY->id, $userOrder->to_user_account_id);

        $userAccountUSD->refresh();
        $this->assertEquals(0, $userAccountUSD->balance);

        $userAccountCNY->refresh();
        $this->assertEquals(738, $userAccountCNY->balance);

        $userOrder->refresh();
        $this->assertEquals(2, $userOrder->userTransactions()->count());

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = $userOrder->userTransactions()->where('type','=', UserTransaction::TYPE_INCOME)->first();
        $this->assertEquals(738, $userTransaction->amount);
        $this->assertEquals(738, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_SUCCESSFUL, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_INCOME, $userTransaction->type);
        $this->assertEquals($userOrder->id, $userTransaction->user_order_id);

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = $userOrder->userTransactions()->where('type','=', UserTransaction::TYPE_EXPENSE)->first();
        $this->assertEquals(100, $userTransaction->amount);
        $this->assertEquals(0, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_SUCCESSFUL, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_EXPENSE, $userTransaction->type);
        $this->assertEquals($userOrder->id, $userTransaction->user_order_id);
    }

    public function testCreateTransferOrder() {
        /**
         * @var UserAccountService $userAccountService
         */
        $userAccountService = app(UserAccountService::class);

        $user1 = $this->createUser();
        /**
         * @var UserAccount $userAccount1
         */
        $userAccount1 = $userAccountService->getUserAccountQuery($user1->id, Currency::CNY)->first();
        $userAccount1->balance = 10000; // 100 yuan
        $userAccount1->save();

        $user2 = $this->createUser();
        /**
         * @var UserAccount $userAccount2
         */
        $userAccount2 = $userAccountService->getUserAccountQuery($user2->id, Currency::CNY)->first();

        /**
         * @var UserOrderService $userOrderService
         */
        $userOrderService = app(UserOrderService::class);

        try {
            $userOrderService->createTransferOrder($userAccount1->id, $user2->email, $user2->nickname, 1000, 'transfer');
            $this->fail();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }

        $userOrder = $userOrderService->createTransferOrder($userAccount1->id, $user2->email, $user2->nickname, 10, 'transfer');

        $this->assertEquals(1000, $userOrder->amount); // 10 yuan
        $this->assertEquals(UserOrder::TYPE_TRANSFER, $userOrder->type);
        $this->assertEquals(UserOrder::STATUS_SUCCESSFUL, $userOrder->status);
        $this->assertEquals($userAccount1->id, $userOrder->user_account_id);
        $this->assertEquals($userAccount2->id, $userOrder->to_user_account_id);

        $this->assertEquals(1, $userOrder->userTransactions()->count());

        $userAccount1->refresh();
        $userAccount2->refresh();
        $this->assertEquals(9000, $userAccount1->balance); // balance 90 yuan
        $this->assertEquals(1000, $userAccount2->balance); //10 yuan

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = UserTransaction::where('user_account_id', '=', $userAccount2->id)->where('type','=', UserTransaction::TYPE_INCOME)->first();
        $this->assertEquals(1000, $userTransaction->amount);
        $this->assertEquals(1000, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_SUCCESSFUL, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_INCOME, $userTransaction->type);
        $this->assertNull($userTransaction->user_order_id);

        /**
         * @var UserTransaction $userTransaction
         */
        $userTransaction = UserTransaction::where('user_account_id', '=', $userAccount1->id)->where('type','=', UserTransaction::TYPE_EXPENSE)->first();
        $this->assertEquals(1000, $userTransaction->amount);
        $this->assertEquals(9000, $userTransaction->balance);
        $this->assertEquals(UserTransaction::STATUS_SUCCESSFUL, $userTransaction->status);
        $this->assertEquals(UserTransaction::TYPE_EXPENSE, $userTransaction->type);
        $this->assertEquals($userOrder->id, $userTransaction->user_order_id);
    }

}
