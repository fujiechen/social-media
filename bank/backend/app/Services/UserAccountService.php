<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Role;
use App\Models\UserAccount;
use App\Models\UserOrder;
use Illuminate\Database\Eloquent\Builder;

class UserAccountService
{
    public function create(int $userId, int $currencyId)
    {
        $currency = Currency::find($currencyId);
        $accountNumber = $currency->name . '-' . time();
        return UserAccount::create([
            'user_id' => $userId,
            'account_number' => $accountNumber,
            'currency_id' => $currencyId,
            'balance' => 0,
            'product_balance' => 0,
        ]);
    }

    public function getUserAccountQuery(int $userId, ?string $currencyName = null): Builder
    {
        $query = UserAccount::query();

        $query->select('user_accounts.*');
        $query->where('user_accounts.user_id', '=', $userId);

        if ($currencyName) {
            $query->join('currencies', 'user_accounts.currency_id', '=', 'currencies.id');
            $query->where('currencies.name', '=', $currencyName);
        }

        return $query;
    }

    public function getCurrencySymbol(int $userAccountId)
    {
        return UserAccount::find($userAccountId)->currency->symbol;
    }

    public function updateBalance(int $userAccountId, int $amountChange): UserAccount
    {
        $userAccount = UserAccount::find($userAccountId);
        $userAccount->balance += $amountChange;

        if ($userAccount->balance < 0) {
            $userAccount->balance = 0;
        }

        $userAccount->save();
        return $userAccount;
    }

    public function updateProductBalance(int $userAccountId, int $amountChange): UserAccount
    {
        $userAccount = UserAccount::find($userAccountId);
        $userAccount->product_balance += $amountChange;

        if ($userAccount->product_balance < 0) {
            $userAccount->product_balance = 0;
        }

        $userAccount->save();
        return $userAccount;
    }

    public function isUserAccountHasOrderTypeEnabled(int $userAccountId, string $orderType): bool {
        /**
         * @var UserAccount $userAccount
         */
        $userAccount = UserAccount::find($userAccountId);

        if ($orderType == UserOrder::TYPE_DEPOSIT) {
            return $userAccount->currency->deposit_enabled;
        }

        if ($orderType == UserOrder::TYPE_PURCHASE) {
            return $userAccount->currency->purchase_enabled;
        }

        if ($orderType == UserOrder::TYPE_WITHDRAW) {
            return $userAccount->currency->withdraw_enabled;
        }

        if ($orderType == UserOrder::TYPE_TRANSFER) {
            return $userAccount->currency->transfer_enabled;
        }

        if ($orderType == UserOrder::TYPE_EXCHANGE) {
            return $userAccount->currency->exchange_enabled;
        }

        return false;
    }
}
