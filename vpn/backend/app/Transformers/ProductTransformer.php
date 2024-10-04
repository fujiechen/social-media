<?php

namespace App\Transformers;

use App\Models\Product;
use App\Utils\Utilities;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer) {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(Product $product): array
    {
        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'currency_name' => $product->currency_name,
            'unit_price' => $product->unit_price,
            'frequency' => $product->frequency,
            'frequency_as_extend_days' => $product->frequency_as_extend_days,
            'created_at_formatted' => $product->created_at_formatted,
            'thumbnail_file' => $product->thumbnailFile ? $this->fileTransformer->transform($product->thumbnailFile) : null,
            'category_name' => $product->category->name,
            'unit_price_formatted' => Utilities::formatCurrency($product->currency_name, $product->unit_cents),
        ];

        $productImages = [];
        foreach ($product->images as $productImage) {
            $productImages[] = $this->fileTransformer->transform($productImage);
        }
        $data['image_files'] = $productImages;

        return $data;
    }


}
