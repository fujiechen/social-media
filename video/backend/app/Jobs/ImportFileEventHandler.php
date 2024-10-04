<?php

namespace App\Jobs;

use App\Models\Platform;
use App\Models\ProductPromotion;
use App\Services\ProductPromotionService;
use App\Services\ProductService;
use Dcat\EasyExcel\Excel;
use Dcat\EasyExcel\Support\SheetCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ImportFileEventHandler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ImportFileEvent $importFileEvent;

    public function __construct(ImportFileEvent $importFileEvent)
    {
        $this->importFileEvent = $importFileEvent;
    }

    public function handle(
        ProductService          $productService,
        ProductPromotionService $productPromotionService
    )
    {
        // TODO: move to s3
        Excel::import($this->importFileEvent->filePath)
            ->disk('local')
            ->first()
            ->chunk(100, function (SheetCollection $rows) use ($productService, $productPromotionService) {
                $rows->each(function ($info) use ($productService, $productPromotionService) {
                    $product = $productService->updateOrCreateForImporting(
                        $this->importFileEvent->userId,
                        $this->importFileEvent->businessId,
                        $info['商品名称'],
                        Platform::DOUYIN_ID,
                        $info['合作者id'],
                        $info['合作者名称'],
                        $info['商品id'],
                    );

                    foreach ($this->importFileEvent->productPromotionTypeIds as $productPromotionTypeId) {
                        $productPromotionService->updateOrCreateForImporting(
                            $this->importFileEvent->userId,
                            $product->id,
                            $productPromotionTypeId,
                            $this->importFileEvent->productPromotionDescription,
                            ProductPromotion::COMMISSION_TYPE_PERCENTAGE,
                            ProductPromotion::COMMISSION_TYPE_PERCENTAGE,
                            $info['推广链接'],
                            floatval($info['商品售价']),
                            floatval($info['佣金率']),
                            floatval($info['服务费率']),
                        );
                    }
                });
            });

        Storage::delete($this->importFileEvent->filePath);
        return true;
    }
}
