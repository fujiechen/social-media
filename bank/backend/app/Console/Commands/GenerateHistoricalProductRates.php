<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductRateService;
use App\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateHistoricalProductRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:historical-product-rate:generate {productId} {--days='.Product::HISTORY_TREND_DAYS.'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate historical product rates for given product id';

    private ProductService $productService;
    private ProductRateService $productRateService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ProductService     $productService,
        ProductRateService $productRateService
    )
    {
        parent::__construct();
        $this->productService = $productService;
        $this->productRateService = $productRateService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('historical-product-rate:generate start!');

        $productId = $this->argument('productId');
        if (is_null($productId)) {
            Log::error('Product ID is not provided!');
            return self::INVALID;
        }

        $days = $this->option('days');

        $product = $this->productService->getProductsQuery((int)$productId)->first();

        if (is_null($product)) {
            $this->error('Cannot find the product!');
            return self::FAILURE;
        }

        $this->info('Generating historical product rates for product ' . $product->name . ' for ' . $days . ' days');

        $this->productRateService->createHistoryProductRates($product->id, $days);
        $product->trend = $this->productRateService->getProductTrend($product->id);
        $product->save();

        $this->info('Product rates generated!');

        return self::SUCCESS;
    }
}
