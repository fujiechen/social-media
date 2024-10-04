<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\IndexUserTransactionRequest;
use App\Http\Resources\UserTransactionResource;
use App\Models\UserTransaction;
use App\Services\UserAccountService;
use App\Services\UserTransactionService;
use App\Utils\Formatter;
use Illuminate\Support\Facades\Auth;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Api
 */
class UserTransactionController extends BaseController
{
    private UserTransactionService $userTransactionService;
    private UserAccountService $userAccountService;

    public function __construct(
        UserTransactionService $userTransactionService,
        UserAccountService $userAccountService
    )
    {
        $this->userTransactionService = $userTransactionService;
        $this->userAccountService = $userAccountService;
    }

    public function index(IndexUserTransactionRequest $request)
    {
        $user = Auth::user();
        $userAccountId = $request->get('user_account_id', null);
        $orderType = $request->get('order_type', null);
        $transactionType = $request->get('transaction_type', null);
        $lastDays = $request->get('last_days', 30);

        $query = $this->userTransactionService
            ->getUserTransactionsQuery($user->id, $userAccountId, $orderType, $transactionType, $lastDays);

        $userTransactions = $query->get();

        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($userTransactions as $userTransaction) {
            if ($userTransaction->type == UserTransaction::TYPE_INCOME) {
                $totalIncome += $userTransaction->amount;
            } else if ($userTransaction->type == UserTransaction::TYPE_EXPENSE) {
                $totalExpense += $userTransaction->amount;
            }
        }

        return UserTransactionResource::collection($userTransactions)->additional([
            'additional' => [
                'total_income' => Formatter::formatAmount($totalIncome, $this->userAccountService->getCurrencySymbol($userAccountId)),
                'total_income_number' => $totalIncome,
                'total_expense' => Formatter::formatAmount($totalExpense, $this->userAccountService->getCurrencySymbol($userAccountId)),
                'total_expense_number' => $totalExpense,
            ]
        ]);
    }
}
