<?php

namespace App\Services;

use App\Dtos\ProductDto;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function updateOrCreateProduct(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $thumbnailFileId = $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id;

            $product = Product::updateOrCreate([
                'id' => $dto->productId
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
                'currency_name' => $dto->currencyName,
                'unit_cents' => $dto->unitPrice * 100,
                'frequency' => $dto->frequency,
                'thumbnail_file_id' => $thumbnailFileId,
                'category_id' => $dto->categoryId,
                'order_num_allowance' => empty($dto->orderNumAllowance) ? null : $dto->orderNumAllowance,
            ]);

            ProductImage::query()->where('product_id', '=', $product->id)->delete();

            foreach ($dto->imageFileDtos as $imageFileDto) {
                $imageFileId = $this->fileService->getOrCreateFile($imageFileDto)->id;

                ProductImage::create([
                    'product_id' => $product->id,
                    'file_id' => $imageFileId
                ]);
            }

            return $product;
        });
    }
}
