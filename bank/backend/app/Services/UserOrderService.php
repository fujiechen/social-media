<?php

namespace App\Services;

use App\Exceptions\IllegalArgumentException;
use App\Models\Role;
use App\Models\User;
use App\Mail\UserOrderDeposit;
use App\Mail\UserOrderWithdraw;
use App\Models\Currency;
use App\Models\Product;
use App\Models\UserAccount;
use App\Models\UserOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserOrderService extends BaseUserService
{
    public const ACTION_COMPLETE_ORDER_AS_SUCCESSFUL = 'completeOrderAsSuccessful';
    public const ACTION_COMPLETE_ORDER_AS_FAILED = 'completeOrderAsFailed';

    private UserTransactionService $userTransactionService;
    private CurrencyRateService $currencyRateService;
    private UserProductService $userProductService;
    private UserAccountService $userAccountService;
    private UserOrderNotificationService $userOrderNotificationService;

    public function __construct(
        UserTransactionService $userTransactionService,
        CurrencyRateService $currencyRateService,
        UserProductService $userProductService,
        UserAccountService $userAccountService,
        TranslationService $translationService,
        UserOrderNotificationService $userOrderNotificationService
    )
    {
        parent::__construct($translationService);
        $this->userTransactionService = $userTransactionService;
        $this->currencyRateService = $currencyRateService;
        $this->userProductService = $userProductService;
        $this->userAccountService = $userAccountService;
        $this->userOrderNotificationService = $userOrderNotificationService;
    }

    public function createPurchaseOrder(int $userId, int $productId, float $amount, ?string $comment = null): UserOrder
    {
        $product = Product::find($productId);

        /**
         * @var UserAccount $userAccount
         */
        $userAccount = $this->userAccountService->getUserAccountQuery($userId, $product->currency->name)->first();

        if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($userAccount->id, UserOrder::TYPE_PURCHASE)) {
            throw new IllegalArgumentException('userAccount', 'purchase is not enabled for this currency');
        }

        $amountInCent = (int)($amount * 100);

        if ($userAccount->balance < $amountInCent) {
            throw new IllegalArgumentException("user_account.balance", $this->t($userId, "You don't have sufficient account balance"));
        }

        if ($amountInCent < $product->start_amount) {
            throw new IllegalArgumentException("user_order.start_amount", $this->t($userId, "Your purchase doesn't meet the minimum amount"));
        }

        if ($product->stock <= 0) {
            throw new IllegalArgumentException("product.stock", $this->t($userId
                , "Unfortunately this investment has been sold out, please try again later"));
        }

        $userOrder = UserOrder::create([
            'user_account_id' => $userAccount->id,
            'product_id' => $productId,
            'type' => UserOrder::TYPE_PURCHASE,
            'amount' => $amountInCent,
            'start_amount' => $product->start_amount,
            'freeze_days' => $product->freeze_days,
            'status' => UserOrder::STATUS_SUCCESSFUL,
            'comment' => $comment,
        ]);

        $this->userTransactionService->createUserOrderExpenseUserTransaction($userOrder->id);
        $this->userProductService->createUserProduct($userOrder->id);

        return $userOrder;
    }

    public function createDepositOrder(
        int $userAccountId,
        float $amount,
        ?string $comment,
        ?array $meta_json,
    ): UserOrder
    {
        if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($userAccountId, UserOrder::TYPE_DEPOSIT)) {
            throw new IllegalArgumentException('userAccount', 'deposit is not enabled for this currency');
        }

        $amountInCent = (int)($amount * 100);

        return UserOrder::create([
            'user_account_id' => $userAccountId,
            'type' => UserOrder::TYPE_DEPOSIT,
            'amount' => $amountInCent,
            'status' => UserOrder::STATUS_PENDING,
            'comment' => $comment,
            'meta_json' => $meta_json,
        ]);
    }

    public function updateUserOrder($action, $userOrderId)
    {
        if ($action == self::ACTION_COMPLETE_ORDER_AS_SUCCESSFUL) {
            $this->completeOrderAsSuccessful($userOrderId);
        } else if ($action == self::ACTION_COMPLETE_ORDER_AS_FAILED) {
            $this->completeOrderAsFailed($userOrderId);
        }

        $userOrder = UserOrder::find($userOrderId);
        if ($userOrder->type === UserOrder::TYPE_DEPOSIT && !empty($userOrder->meta_json)) {
            $this->userOrderNotificationService->createUserOrderNotification($userOrderId);
        }
    }

    private function completeOrderAsFailed($userOrderId)
    {
        $userOrder = UserOrder::find($userOrderId);
        $userOrder->status = UserOrder::STATUS_FAILED;
        $userOrder->save();

        $user = $userOrder->userAccount->user;

        // failed withdraw should bring the money back
        if ($userOrder->type === UserOrder::TYPE_WITHDRAW) {
            $this->userTransactionService->createUserOrderIncomeUserTransaction($userOrderId);
            //Mail::to($user->email)->queue(new UserOrderWithdraw($user, $userOrder));
        } else if ($userOrder->type === UserOrder::TYPE_DEPOSIT) {
            $this->userTransactionService->createUserOrderIncomeUserTransaction($userOrderId);
            //Mail::to($user->email)->queue(new UserOrderDeposit($user, $userOrder));
        }

        return $userOrder;
    }

    private function completeOrderAsSuccessful($userOrderId)
    {
        Log::info('complete order to success ' . $userOrderId);
        $userOrder = UserOrder::find($userOrderId);

        if (UserOrder::STATUS_SUCCESSFUL == $userOrder->status) {
            return $userOrder;
        }

        $userOrder->status = UserOrder::STATUS_SUCCESSFUL;
        $userOrder->save();

//        $user = $userOrder->userAccount->user;
        if ($userOrder->type === UserOrder::TYPE_DEPOSIT) {
            $this->userTransactionService->createUserOrderIncomeUserTransaction($userOrderId);
            //Mail::to($user->email)->queue(new UserOrderDeposit($user, $userOrder));
        } else if ($userOrder->type === UserOrder::TYPE_WITHDRAW) {
            //Mail::to($user->email)->queue(new UserOrderWithdraw($user, $userOrder));
        } else if ($userOrder->type === UserOrder::TYPE_TRANSFER) {
            $this->userTransactionService->createUserOrderExpenseUserTransaction($userOrder->id, $userOrder->amount);
            $this->userTransactionService->createNoUserOrderIncomeUserTransaction($userOrder->to_user_account_id, $userOrder->amount, $userOrder->comment);
        }

        return $userOrder;
    }

    public function createExchangeOrder(int $fromUserAccountId, int $toUserAccountId, float $amount): UserOrder
    {
        /**
         * @var UserAccount $fromUserAccount
         */
        $fromUserAccount = UserAccount::find($fromUserAccountId);

        /**
         * @var UserAccount $toUserAccount
         */
        $toUserAccount = UserAccount::find($toUserAccountId);

        if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($fromUserAccountId, UserOrder::TYPE_EXCHANGE)) {
            throw new IllegalArgumentException('userAccount', 'exchange is not enabled for this currency');
        }

        if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($toUserAccountId, UserOrder::TYPE_EXCHANGE)) {
            throw new IllegalArgumentException('userAccount', 'exchange is not enabled for this currency');
        }

        //get exchange rate by currency
        $exchangeRate = $this->currencyRateService->getExchangeRate($fromUserAccount->currency_id, $toUserAccount->currency_id);
        $comment = $fromUserAccount->currency->symbol . '1' . ' = ' . $toUserAccount->currency->symbol . $exchangeRate;

        $exchangeAmount = $this->currencyRateService->exchange($fromUserAccount->currency_id, $toUserAccount->currency_id, $amount);

        $fromAmountInCent = intval(number_format($amount * 100, 0, '.', ''));

        if ($fromUserAccount->balance < $fromAmountInCent) {
            throw new IllegalArgumentException("user_account.balance", $this->t($fromUserAccount->user->id, "You don't have sufficient account balance"));
        }

        $toAmountInCent = intval(number_format($exchangeAmount * 100, 0, '.', ''));

        $userOrder = UserOrder::create([
            'user_account_id' => $fromUserAccount->id,
            'to_user_account_id' => $toUserAccount->id,
            'type' => UserOrder::TYPE_EXCHANGE,
            'amount' => $fromAmountInCent,
            'status' => UserOrder::STATUS_SUCCESSFUL,
            'comment' => $comment,
        ]);

        $this->userTransactionService->createUserOrderExpenseUserTransaction($userOrder->id, $fromAmountInCent, $fromUserAccountId);
        $this->userTransactionService->createUserOrderIncomeUserTransaction($userOrder->id, $toAmountInCent, $toUserAccountId);

        return $userOrder;
    }

    public function createTransferOrder(
        int $fromUserAccountId,
        string $toUserEmail,
        string $toUserNickname,
        float $amount,
        ?string $comment = null,
        ?array $meta_json = null,
    ): UserOrder
    {
        /**
         * @var UserAccount $fromUserAccount
         */
        $fromUserAccount = UserAccount::find($fromUserAccountId);
        $fromUser = $fromUserAccount->user;

        /**
         * @var User $toUser
         */
        $toUser = User::query()
            ->where('email', '=', $toUserEmail)
            ->where('nickname', '=', $toUserNickname)
            ->first();

        if (empty($toUser)) {
            throw new IllegalArgumentException("user.fromUser", $this->t($fromUserAccount->user->id, "To user doesn't exist"));
        }

        $toUserAccount = UserAccount::where('user_id', '=', $toUser->id)
            ->where('currency_id', '=', $fromUserAccount->currency_id)->first();

        /**
         * For admin & agent role, disable transfer check of from account and to account
         */
        if ($fromUser->hasRoleId(Role::ROLE_ADMINISTRATOR_ID)
            || $fromUser->hasRoleId(Role::ROLE_AGENT_ID)
            || $toUser->hasRoleId(Role::ROLE_ADMINISTRATOR_ID)
            || $toUser->hasRoleId(Role::ROLE_AGENT_ID)) {
        } else {
            if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($fromUserAccountId, UserOrder::TYPE_EXCHANGE)) {
                throw new IllegalArgumentException('userAccount', 'transfer is not enabled for this currency');
            }
        }

        $amountInCent = (int)($amount * 100);
        if ($fromUserAccount->balance < $amountInCent) {
            throw new IllegalArgumentException("user_account.balance", $this->t($fromUserAccount->user->id, "You don't have sufficient account balance"));
        }

        $comment = $comment . ' (' . $fromUserAccount->user->nickname . ')';

        $userOrder = UserOrder::create([
            'user_account_id' => $fromUserAccount->id,
            'to_user_account_id' => $toUserAccount->id,
            'type' => UserOrder::TYPE_TRANSFER,
            'amount' => $amountInCent,
            'status' => UserOrder::STATUS_PENDING,
            'comment' => $comment,
            'meta_json' => $meta_json,
        ]);

        $this->updateUserOrder(self::ACTION_COMPLETE_ORDER_AS_SUCCESSFUL, $userOrder->id);

        $userOrder->refresh();

        return $userOrder;
    }


    public function createWithdrawOrder(int $userAccountId, float $amount, ?int $userAddressId = null, ?int $userWithdrawAccountId = null, ?string $comment = null)
    {
        if (!$this->userAccountService->isUserAccountHasOrderTypeEnabled($userAccountId, UserOrder::TYPE_EXCHANGE)) {
            throw new IllegalArgumentException('userAccount', 'withdraw is not enabled for this currency');
        }

        $userAccount = UserAccount::find($userAccountId);
        $user = $userAccount->user;

        $amountInCent = (int)($amount * 100);

        if ($userAccount->balance <= 0) {
            throw new IllegalArgumentException("user_account.balance", $this->t($user->id, "You don't have sufficient account balance"));
        }

        // make sure the order also log the correct withdrawal amount if not enough
        if ($userAccount->balance < $amountInCent) {
            $amountInCent = $userAccount->balance;
        }

        $userCompletedProductsCount = $this->userProductService->getUserProductsQuery($user->id, false)->count();
        if ($userCompletedProductsCount <= 0) {
            throw new IllegalArgumentException("user.products", $this->t($user->id
                , "In order to prevent from money laundry, you must complete at least one investment"));
        }

        // Wired Min: 1,000 USD
        // Cash Min: 100,000 USD
        $currency = Currency::where('name', '=', Currency::USD)->first();
        $eqUsdAmount = $this->currencyRateService->exchange($userAccount->currency_id, $currency->id, $amount);
        if (!is_null($userAddressId) && $eqUsdAmount < UserOrder::WITHDRAW_CASH_MIN_USD) {
            throw new IllegalArgumentException("user.amount", $this->t($user->id,
                "Cash withdraw service requires minimum equivalent US$100,000"));
        } elseif (!is_null($userWithdrawAccountId) && $eqUsdAmount < UserOrder::WITHDRAW_WIRED_MIN_USD) {
            throw new IllegalArgumentException("user.amount", $this->t($user->id,
                "Wired withdraw service requires minimum equivalent US$1,000"));
        }

        $userOrder = UserOrder::create([
            'user_account_id' => $userAccount->id,
            'type' => UserOrder::TYPE_WITHDRAW,
            'amount' => $amountInCent,
            'to_user_withdraw_account_id' => $userWithdrawAccountId,
            'to_user_address_id' => $userAddressId,
            'status' => UserOrder::STATUS_PENDING,
            'comment' => $comment,
        ]);

        $this->userTransactionService->createUserOrderExpenseUserTransaction($userOrder->id);

        return $userOrder;
    }

    public function getOrdersQuery(int $userId, ?int $userAccountId = null, ?string $type = null, ?int $productId = null)
    {
        $query = UserOrder::query();
        $query->select('user_orders.*');
        $query->join('user_accounts', 'user_accounts.id', '=', 'user_orders.user_account_id');
        $query->where('user_accounts.user_id', '=', $userId);
        $query->orderBy('user_orders.created_at', 'desc');

        if (!is_null($userAccountId)) {
            $query->where('user_account_id', '=', $userAccountId);
        }

        if (!is_null($type)) {
            $query->where('type', '=', $type);
        }

        if (!is_null($productId)) {
            $query->where('product_id', '=', $productId);
        }

        return $query;
    }

    public function getTransferredUsersQuery(int $userId): Builder
    {
        $query = UserOrder::query();
        $query->select('users.*');
        $query->join('user_accounts as sender', 'user_orders.user_account_id', '=', 'sender.id');
        $query->join('user_accounts as receiver', 'user_orders.to_user_account_id', '=', 'receiver.id');
        $query->join('users', 'receiver.user_id', '=', 'users.id');
        $query->where('sender.user_id', '=', $userId);
        $query->where('user_orders.type', '=', UserOrder::TYPE_TRANSFER);
        $query->groupBy('receiver.user_id');
        return $query;
    }

    public function getDepositOrdersToCloseQuery($minutes = 15): Builder {
        $now = Carbon::now();
        $now->subMinutes($minutes);

        return UserOrder::query()
            ->where('type', '=', UserOrder::TYPE_DEPOSIT)
            ->where('status', '=', UserOrder::STATUS_PENDING)
            ->where('created_at', '<', $now);
    }
}
