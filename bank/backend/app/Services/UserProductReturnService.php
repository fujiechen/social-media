<?php

namespace App\Services;

use App\Models\ProductRate;
use App\Models\UserOrder;
use App\Models\UserProduct;
use App\Models\UserProductReturn;

class UserProductReturnService
{
    private UserAccountService $userAccountService;

    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    public function createUserProductReturn(int $userProductId, ?int $productRateId = null): UserProductReturn
    {
        $lastUserProductReturn = UserProductReturn::where('user_product_id', '=', $userProductId)
            ->orderBy('id', 'desc')->limit(1)->first();

        if (is_null($productRateId)) {
            $userOrder = UserProduct::find($userProductId)->userOrder;

            //initialize first return
            if (is_null($lastUserProductReturn)) {
                $userProductReturn = UserProductReturn::create([
                    'user_product_id' => $userProductId,
                    'product_rate_id' => null,
                    'market_value' => $userOrder->amount,
                    'book_cost' => $userOrder->amount,
                    'comment' => UserOrder::TYPE_PURCHASE . ' ' . $userOrder->product->title
                ]);

                $this->userAccountService->updateProductBalance($userOrder->user_account_id, $userOrder->amount);

            } else {
                // withdraw all from product
                $userProductReturn = UserProductReturn::create([
                    'user_product_id' => $userProductId,
                    'product_rate_id' => null,
                    'market_value' => 0,
                    'book_cost' => 0,
                    'comment' => UserOrder::TYPE_WITHDRAW . ' ' . $userOrder->product->title
                ]);
            }
        } else {

            // regular earning
            if (is_null($lastUserProductReturn)) {
                throw new \Exception('no last return found');
            }

            $productRate = ProductRate::find($productRateId);

            if (is_null($productRate)) {
                throw new \Exception('no today product rate found');
            }

            $returnAmountInCentInFloat = $lastUserProductReturn->market_value * $productRate->rate / 10000;
            $returnAmountInCent = intval(number_format($returnAmountInCentInFloat, 0, '.', ''));
            $marketValue = $lastUserProductReturn->market_value + $returnAmountInCent;
            if ($marketValue < 0) {
                $marketValue = 0;
            }

            $trend = $productRate->rate > 0 ? '+' : '';

            $userProductReturn = UserProductReturn::create([
                'user_product_id' => $userProductId,
                'product_rate_id' => $productRate->id,
                'market_value' => $marketValue,
                'book_cost' => $lastUserProductReturn->book_cost,
                'comment' => $trend . number_format($productRate->rate / 100, 2) . '%, '
                    . $trend . number_format($returnAmountInCent / 100, 2) . $productRate->product->currency->symbol,
            ]);

            $userAccountId = $userProductReturn->userProduct->userOrder->userAccount->id;
            $this->userAccountService->updateProductBalance($userAccountId, $returnAmountInCent);
        }

        return $userProductReturn;
    }

    public function getUserProductReturnsQuery(int $userId, int $userProductId, string $orderByDirection = 'desc')
    {
        $query = UserProductReturn::query();
        $query->select('user_product_returns.*');
        $query->join('user_products', 'user_product_returns.user_product_id', '=', 'user_products.id');
        $query->join('user_orders', 'user_products.user_order_id', '=', 'user_orders.id');
        $query->join('user_accounts', 'user_accounts.id', '=', 'user_orders.user_account_id');
        $query->where('user_accounts.user_id', '=', $userId);
        $query->where('user_product_returns.user_product_id', '=', $userProductId);
        $query->orderBy('user_product_returns.updated_at', $orderByDirection);
        return $query;
    }
}
