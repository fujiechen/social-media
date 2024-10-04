<?php

namespace App\Services;

use App\Models\UserAccount;
use App\Models\UserOrder;
use App\Models\UserTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class UserTransactionService
{
    private UserAccountService $userAccountService;

    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    public function createIncomeUserTransactionFromCompletedUserProduct(int $userAccountId, int $totalMarketValue, ?string $comment = null)
    {
        $userAccount = UserAccount::find($userAccountId);
        $this->userAccountService->updateBalance($userAccountId, $totalMarketValue);
        $this->userAccountService->updateProductBalance($userAccountId, -$totalMarketValue);
        $userAccount->refresh();

        return UserTransaction::create([
            'type' => UserTransaction::TYPE_INCOME,
            'user_account_id' => $userAccountId,
            'user_order_id' => null,
            'amount' => $totalMarketValue,
            'balance' => $userAccount->balance,
            'status' => UserTransaction::STATUS_SUCCESSFUL,
            'comment' => $comment,
        ]);
    }

    public function createNoUserOrderIncomeUserTransaction(int $userAccountId, int $amountInCent, string $comment)
    {

        $userAccount = $this->userAccountService->updateBalance($userAccountId, $amountInCent);

        return UserTransaction::create([
            'type' => UserTransaction::TYPE_INCOME,
            'user_account_id' => $userAccountId,
            'amount' => $amountInCent,
            'balance' => $userAccount->balance,
            'status' => UserTransaction::STATUS_SUCCESSFUL,
            'comment' => $comment,
        ]);
    }

    public function createUserOrderIncomeUserTransaction(int $userOrderId, ?int $amountInCent = null, ?int $userAccountId = null)
    {
        Log::info('create order transaction of order ' . $userOrderId);

        /**
         * @var UserOrder $userOrder
         */
        $userOrder = UserOrder::find($userOrderId);
        if ($userOrder->status == UserOrder::STATUS_SUCCESSFUL) {
            $status = UserTransaction::STATUS_SUCCESSFUL;
        } else {
            $status = UserTransaction::STATUS_FAILED;
        }


        $transactionAmountInCent = $amountInCent;
        if (is_null($amountInCent)) {
            $transactionAmountInCent = $userOrder->amount;
        }

        $transactionUserAccountId = $userAccountId;
        if (is_null($userAccountId)) {
            $transactionUserAccountId = $userOrder->user_account_id;
        }

        // withdraw order should refund processing fee
        if ($userOrder->type === UserOrder::TYPE_WITHDRAW) {
            // Wired Min: 1,000 USD, 5% processing fee
            // Cash Min: 100,000 USD, 20% processing fee
            if (!is_null($userOrder->to_user_withdraw_account_id)) {
                $processingFeeInCents = $transactionAmountInCent * UserOrder::WITHDRAW_WIRED_PROCESSING_FEE_RATE;
            } else {
                $processingFeeInCents = $transactionAmountInCent * UserOrder::WITHDRAW_CASH_PROCESSING_FEE_RATE;
            }
            $actualTransactionAmountInCent = $transactionAmountInCent - $processingFeeInCents;

            $userAccount = $this->userAccountService->updateBalance($transactionUserAccountId, $processingFeeInCents);

            UserTransaction::create([
                'type' => UserTransaction::TYPE_INCOME,
                'user_account_id' => $transactionUserAccountId,
                'user_order_id' => $userOrder->id,
                'comment' => $userOrder->type,
                'amount' => $processingFeeInCents,
                'balance' => $userAccount->balance,
                'status' => UserTransaction::STATUS_SUCCESSFUL,
            ]);
        } else {
            $actualTransactionAmountInCent = $transactionAmountInCent;
        }

        if ($status == UserTransaction::STATUS_SUCCESSFUL) {
            $userAccount = $this->userAccountService->updateBalance($transactionUserAccountId, $actualTransactionAmountInCent);
        } else {
            $userAccount = $userOrder->userAccount;
        }

        return UserTransaction::create([
            'type' => UserTransaction::TYPE_INCOME,
            'user_account_id' => $transactionUserAccountId,
            'user_order_id' => $userOrderId,
            'amount' => $actualTransactionAmountInCent,
            'balance' => $userAccount->balance,
            'status' => $status,
            'comment' => $userOrder->type,
        ]);
    }

    public function createUserOrderExpenseUserTransaction(int $userOrderId, ?int $amountInCent = null, ?int $userAccountId = null)
    {
        $userOrder = UserOrder::find($userOrderId);

        $transactionAmountInCent = $amountInCent;
        if (is_null($amountInCent)) {
            $transactionAmountInCent = $userOrder->amount;
        }

        $transactionUserAccountId = $userAccountId;
        if (is_null($userAccountId)) {
            $transactionUserAccountId = $userOrder->user_account_id;
        }

        // withdraw order should charge processing fee
        if ($userOrder->type === UserOrder::TYPE_WITHDRAW) {
            // Wired Min: 1,000 USD, 5% processing fee
            // Cash Min: 100,000 USD, 20% processing fee
            if (!is_null($userOrder->to_user_withdraw_account_id)) {
                $processingFeeInCents = $transactionAmountInCent * UserOrder::WITHDRAW_WIRED_PROCESSING_FEE_RATE;
            } else {
                $processingFeeInCents = $transactionAmountInCent * UserOrder::WITHDRAW_CASH_PROCESSING_FEE_RATE;
            }
            $actualTransactionAmountInCent = $transactionAmountInCent - $processingFeeInCents;

            $userAccount = $this->userAccountService->updateBalance($transactionUserAccountId, -$processingFeeInCents);

            UserTransaction::create([
                'type' => UserTransaction::TYPE_EXPENSE,
                'user_account_id' => $transactionUserAccountId,
                'user_order_id' => $userOrder->id,
                'comment' => $userOrder->type,
                'amount' => $processingFeeInCents,
                'balance' => $userAccount->balance,
                'status' => UserTransaction::STATUS_SUCCESSFUL,
            ]);
        } else {
            $actualTransactionAmountInCent = $transactionAmountInCent;
        }

        $userAccount = $this->userAccountService->updateBalance($transactionUserAccountId, -$actualTransactionAmountInCent);

        return UserTransaction::create([
            'type' => UserTransaction::TYPE_EXPENSE,
            'user_account_id' => $transactionUserAccountId,
            'user_order_id' => $userOrder->id,
            'comment' => $userOrder->type,
            'amount' => $actualTransactionAmountInCent,
            'balance' => $userAccount->balance,
            'status' => UserTransaction::STATUS_SUCCESSFUL,
        ]);
    }


    public function getUserTransactionsQuery(
        int     $userId,
        ?int    $userAccountId = null,
        ?string $orderType = null,
        ?string $transactionType = null,
        ?int    $lastDays = null
    )
    {
        $query = UserTransaction::query();
        $query->select('user_transactions.*');
        $query->join('user_accounts', 'user_accounts.id', '=', 'user_transactions.user_account_id');
        $query->where('user_accounts.user_id', '=', $userId);
        $query->orderBy('user_transactions.created_at', 'desc');

        if (!is_null($userAccountId)) {
            $query->where('user_account_id', '=', $userAccountId);
        }

        if (!is_null($orderType)) {
            $query->join('user_orders', 'user_orders.id', '=', 'user_transactions.user_order_id');
            $query->where('user_orders.type', '=', $orderType);
        }

        if (!is_null($transactionType)) {
            $query->where('user_transactions.type', '=', $transactionType);
        }

        if (!is_null($lastDays)) {
            $minDay = Carbon::now()->addDays(-$lastDays)->format('Y-m-d');
            $query->where('user_transactions.created_at', '>=', $minDay);
        }

        return $query;
    }
}
