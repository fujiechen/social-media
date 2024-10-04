<?php

namespace App\Transformers;

use App\Models\Product;
use App\Services\OrderService;
use App\Utils\Utilities;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private UserTransformer $userTransformer;
    private MediaTransformer $mediaTransformer;
    private OrderService $orderService;

    public function __construct(
        FileTransformer $fileTransformer,
        UserTransformer $userTransformer,
        MediaTransformer $mediaTransformer,
        OrderService $orderService) {
            $this->fileTransformer = $fileTransformer;
            $this->userTransformer = $userTransformer;
            $this->mediaTransformer = $mediaTransformer;
            $this->orderService = $orderService;
    }

    public function transform(?Product $product): array
    {
        if (empty($product)) {
            return [];
        }

        $data = [
            'id' => $product->id,
            'name' => $product->name,
            'type' => $product->type,
            'product_user_type' => $product->product_user_type,
            'description' => $product->description,
            'currency_name' => $product->currency_name,
            'unit_price' => $product->unit_price,
            'unit_price_formatted' => Utilities::formatCurrency($product->currency_name, $product->unit_cents),
            'created_at_formatted' => $product->created_at_formatted,
            'thumbnail_file' => $product->thumbnailFile ? $this->fileTransformer->transform($product->thumbnailFile) : null,
        ];

        $productImages = [];
        foreach ($product->images as $productImage) {
            $productImages[] = $this->fileTransformer->transform($productImage);
        }
        $data['image_files'] = $productImages;

        if ($product->type == Product::TYPE_MEDIA) {
            $data['media_id'] = $product->media_id;
            $data['frequency'] = $product->frequency;
            $data['frequency_in_days'] = $product->frequency_as_extend_days;
            $data['media'] = $this->mediaTransformer->transform($product->media);
        } else if ($product->type == Product::TYPE_MEMBERSHIP) {
            $data['role_id'] = $product->role_id;
            $data['frequency'] = $product->frequency;
            $data['frequency_in_days'] = $product->frequency_as_extend_days;
        } else if ($product->type == Product::TYPE_SUBSCRIPTION) {
            $data['publisher_user_id'] = $product->publisher_user_id;
            $data['frequency'] = $product->frequency;
            $data['frequency_in_days'] = $product->frequency_as_extend_days;
        }

        $data['media_product_bought'] = false;
        if (auth('api')->check()) {
            if (!empty($product->media_id)) {
                $user = auth('api')->user();
                $data['media_product_bought'] = $this->orderService->hasProductBought($user->id, $product->id);
            }
        }

        if ($product->user_id) {
            $data['user'] = $this->userTransformer->transform($product->user);
        }

        return $data;
    }


}
