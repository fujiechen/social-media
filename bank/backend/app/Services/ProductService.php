<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;

class ProductService
{
    private ProductRateService $productRateService;

    public function __construct(ProductRateService $productRateService)
    {
        $this->productRateService = $productRateService;
    }

    public function create(
        int     $productCategoryId,
        int     $currencyId,
        string  $title,
        string  $name,
        string  $description,
        int     $startAmount,
        int     $stock,
        int $freezeDays,
        ?string $fundAssets = null,
        ?string $fundFact = null,
        ?string $prospectus = null,
        ?bool   $isRecommend = false,
        ?int    $estimateRate = null,
        ?Carbon $deactivatedAt = null
    )
    {
        $startAmountInCent = $startAmount * 100;
        $product = Product::create([
            'product_category_id' => $productCategoryId,
            'currency_id' => $currencyId,
            'title' => $title,
            'name' => $name,
            'description' => $description,
            'start_amount' => $startAmountInCent,
            'stock' => $stock,
            'freeze_days' => $freezeDays,
            'fund_assets' => $fundAssets,
            'fund_fact_url' => $fundFact,
            'is_recommend' => $isRecommend,
            'prospectus_url' => $prospectus,
            'estimate_rate' => $estimateRate,
            'deactivated_at' => $deactivatedAt,
        ]);

        $this->productRateService->createHistoryProductRates($product->id, Product::HISTORY_TREND_DAYS);
        $product->trend = $this->productRateService->getProductTrend($product->id);
        $product->save();

        return $product;
    }

    public function getProductsQuery(
        ?int   $productId = null,
        ?int   $currencyId = null,
        ?bool  $isRecommend = null,
        string $orderBy = 'id',
        string $sort = 'desc',
        ?bool  $isActive = null
    )
    {
        $query = Product::query();

        if (!is_null($productId)) {
            $query->where('id', '=', $productId);
        }

        if (!is_null($currencyId)) {
            $query->where('currency_id', '=', $currencyId);
        }

        if (!is_null($isRecommend)) {
            $query->where('is_recommend', '=', $isRecommend ? 1 : 0);
        }

        if (!is_null($isActive)) {
            if ($isActive) {
                $query->whereNull('deactivated_at');
            } else {
                $query->whereNotNull('deactivated_at');
            }
        }

        $query->orderBy($orderBy, $sort);

        return $query;
    }
}
