<?php

namespace App\Console\Commands;

use App\Services\ProductRateService;
use App\Services\ProductService;
use App\Services\UserProductService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateTodayProductRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:product-rate-and-return:generate
    {--date= : choose the date to generate, YYYY-MM-DD format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate product rate and user product return for today';

    private ProductService $productService;
    private UserProductService $userProductService;
    private ProductRateService $productRateService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ProductService     $productService,
        UserProductService $userProductService,
        ProductRateService $productRateService
    )
    {
        parent::__construct();
        $this->userProductService = $userProductService;
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
        Log::info('product-rate-and-return:generate start!');

        $date = $this->option('date');
        if ($date) {
            $now = Carbon::createFromFormat('Y-m-d', $date);
        } else {
            $now = Carbon::now();
        }

        Log::info('Generate product rate for ' . $now->toDateString());
        Log::info('Generate product rate at ' . $now->toDateTimeString());

        $this->productService->getProductsQuery()
            ->whereNull('deactivated_at')
            ->orWhere('deactivated_at', '>', $now)
            ->chunk(100, function ($products) use ($now) {
                foreach ($products as $product) {
                    $this->productRateService->createTodayProductRate($product->id, $now);
                    $this->productRateService->getProductTrend($product->id);
                    $product->trend = $this->productRateService->getProductTrend($product->id);
                    $product->save();
                }
            });

        Log::info('Generate product rate finished at ' . Carbon::now()->toDateTimeString());

        Log::info('Processing user products at ' . Carbon::now()->toDateTimeString());
        $this->userProductService->getUserProductsQuery(null, true)
            ->chunk(100, function ($userProducts) use ($now) {
                foreach ($userProducts as $userProduct) {
                    try {
                        $this->userProductService->processUserProduct($userProduct->id, $now);
                    } catch (\Exception $e) {
                    }
                }
            });
        Log::info('Processed user products at ' . Carbon::now()->toDateTimeString());
        Log::info('product-rate-and-return:generate done!');
        return 0;
    }
}
