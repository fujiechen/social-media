<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductRate;
use Carbon\Carbon;

class ProductRateService
{
    public function createHistoryProductRates(int $productId, int $days)
    {
        for ($i = $days; $i >= 0; $i--) {
            $now = Carbon::now();
            $date = $now->subDays($i);
            $this->createTodayProductRate($productId, $date);
        }
    }

    public function createTodayProductRate(int $productId, Carbon $now)
    {
        /** @var ?Product $product */
        $product = Product::find($productId);

        if (is_null($product) || (!is_null($product->deactivated_at) && $product->deactivated_at->isBefore($now))) {
            return null;
        }

        $productRate = ProductRate::query()
            ->where('product_id', $product->id)
            ->where('created_at', $now->format('Y-m-d'))
            ->first();

        if (!is_null($productRate)) {
            return $productRate;
        }

        $yesterdayProductRate = ProductRate::where('product_id', '=', $productId)->orderBy('id', 'desc')->first();

        $todayValue = 100;
        $rate = 0;

        if (!is_null($yesterdayProductRate)) {
            $todayValue = $yesterdayProductRate->value;
            if ($product->start_amount / 100 <= 1000) {
                $rate = rand(-3, 3); // n < $1000, daily rate [-0.05% - 0.05%] annual [-11.52% - 11.52%]
            } else if ($product->start_amount / 100 <= 5000) {
                $rate = rand(-2, 2); // $1000 < n < $5000, daily rate [-0.03% - 0.06%] annual [-7.55% - 7.55%]
            } else {
                $rate = rand(-1, 1); // n > $5000, daily rate [-0.02% - 0.02%] annual [-3.71% - 3.71%]
            }
            $todayValue = $todayValue * (1 + $rate / 10000);
        }

        return ProductRate::create([
            'product_id' => $productId,
            'created_at' => $now->format('Y-m-d'),
            'rate' => $rate,
            'value' => $todayValue,
        ]);
    }

    public function getProductTrend(int $productId): string
    {
        $productRate = ProductRate::where('product_id', '=', $productId)->orderBy('id', 'desc')->first();
        return $productRate->rate >= 0 ? Product::TREND_UP : Product::TREND_DOWN;
    }
}
