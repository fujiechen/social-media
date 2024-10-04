<?php

namespace App\Services;

use App\Models\ProductRate;
use App\Models\UserOrder;
use App\Models\UserProduct;
use App\Models\UserProductReturn;
use Carbon\Carbon;

class UserProductService
{
    private UserProductReturnService $userProductReturnService;
    private UserTransactionService $userTransactionService;

    public function __construct(UserProductReturnService $userProductReturnService, UserTransactionService $userTransactionService)
    {
        $this->userProductReturnService = $userProductReturnService;
        $this->userTransactionService = $userTransactionService;
    }

    public function createUserProduct(int $userOrderId)
    {
        $userOrder = UserOrder::find($userOrderId);
        $userProduct = UserProduct::create([
            'user_order_id' => $userOrderId,
            'is_active' => true,
            'total_market_value' => $userOrder->amount,
            'total_book_cost' => $userOrder->amount,
        ]);

        $userProductReturn = $this->userProductReturnService->createUserProductReturn($userProduct->id);
        $this->updateTotalMarketValue($userProduct->id, $userProductReturn->market_value);
        $this->updateTotalBookCost($userProduct->id, $userProductReturn->book_cost);

        return $userProduct;
    }

    public function completeUserProduct(int $userProductId, Carbon $now): UserProductReturn
    {
        /** @var UserProduct $userProduct */
        $userProduct = UserProduct::find($userProductId);
        $userProduct->is_active = false;
        $userProduct->save();

        $lastUserProductReturn = UserProductReturn::where('user_product_id', '=', $userProduct->id)
            ->orderBy('id', 'desc')->limit(1)->first();
        $totalMarketValue = $lastUserProductReturn->market_value;

        $userProductReturn = $this->userProductReturnService->createUserProductReturn($userProduct->id);
        $userProduct->refresh();

        $this->userTransactionService->createIncomeUserTransactionFromCompletedUserProduct($userProduct->userOrder->userAccount->id, $totalMarketValue, $userProductReturn->comment);

        $this->updateUserProductValueRecordWhenCompleting($userProduct->id);
        $this->updateUserOrderValueRecordWhenCompleting($userProduct->id, $now);

        return $userProductReturn;
    }

    public function processUserProduct(int $userProductId, Carbon $now): ?UserProductReturn
    {
        /** @var UserProduct $userProduct */
        $userProduct = UserProduct::find($userProductId);
        if (is_null($userProduct)) {
            return null;
        }

        $userOrder = $userProduct->userOrder;

        if (
            $userOrder->getReleaseDate()->format('Y-m-d') == $now->format('Y-m-d') ||
            (!is_null($userOrder->product->deactivated_at) && $userOrder->product->deactivated_at->isBefore($now))
        ) {
            $this->completeUserProduct($userProductId, $now);
            $userProduct->refresh();
        }

        if (!$userProduct->is_active) {
            return null;
        }

        $productRate = ProductRate::where('product_id', '=', $userProduct->userOrder->product_id)
            ->where('created_at', '=', $now->format('Y-m-d'))->first();

        if (empty($productRate)) {
            throw new \Exception("No product rate found");
        }

        $userProductReturn = UserProductReturn::query()
            ->where('user_product_id', $userProduct->id)
            ->where('product_rate_id', $productRate->id)
            ->first();

        if (!is_null($userProductReturn)) {
            return $userProductReturn;
        }

        $userProductReturn = $this->userProductReturnService->createUserProductReturn($userProductId, $productRate->id);
        $this->updateTotalMarketValue($userProduct->id, $userProductReturn->market_value);
        $this->updateTotalBookCost($userProduct->id, $userProductReturn->book_cost);

        return $userProductReturn;
    }

    public function getUserProductsQuery(?int $userId = null, ?bool $isActive = null, ?int $userOrderId = null)
    {
        $query = UserProduct::query();
        $query->select('user_products.*');
        $query->join('user_orders', 'user_products.user_order_id', '=', 'user_orders.id');
        $query->join('products', 'user_orders.product_id', '=', 'products.id');
        $query->join('user_accounts', 'user_accounts.id', '=', 'user_orders.user_account_id');

        if (!is_null($userId)) {
            $query->where('user_accounts.user_id', '=', $userId);
        }

        if (!is_null($userOrderId)) {
            $query->where('user_orders.id', '=', $userOrderId);
        }

        if (!is_null($isActive)) {
            $query->where('user_products.is_active', '=', $isActive);
        }

        $query->orderBy('user_products.created_at', 'desc');
        return $query;
    }

    public function updateUserProductValueRecordWhenCompleting(int $userProductId)
    {
        /** @var UserProduct $userProduct */
        $userProduct = UserProduct::find($userProductId);

        $userProduct->final_market_value = $userProduct->total_market_value;
        $userProduct->final_book_cost = $userProduct->total_book_cost;
        $userProduct->total_market_value = 0;
        $userProduct->total_book_cost = 0;

        $userProduct->save();

        return $userProduct;
    }

    public function updateUserOrderValueRecordWhenCompleting(int $userProductId, Carbon $now)
    {
        /** @var UserProduct $userProduct */
        $userProduct = UserProduct::find($userProductId);
        $userOrder = $userProduct->userOrder;

        // if complete before the actual freeze days
        // we should update the freeze days on user order
        if ($userOrder->getReleaseDate()->format('Y-m-d') !== $now->format('Y-m-d')) {
            $userOrder->freeze_days = $now->diffInDays($userOrder->created_at);
            $userOrder->save();
        }

        return $userProduct->refresh();
    }

    public function updateTotalMarketValue(int $userProductId, int $totalMarketValue)
    {
        $userProduct = UserProduct::find($userProductId);
        $userProduct->total_market_value = $totalMarketValue;
        $userProduct->save();
        return $userProduct;
    }

    public function updateTotalBookCost(int $userProductId, int $totalBookCost)
    {
        $userProduct = UserProduct::find($userProductId);
        $userProduct->total_book_cost = $totalBookCost;
        $userProduct->save();
        return $userProduct;
    }
}
