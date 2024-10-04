<?php

namespace App\Transformers;

use App\Models\OrderProduct;
use League\Fractal\TransformerAbstract;

class OrderProductTransformer extends TransformerAbstract
{
    private ProductTransformer $productTransformer;

    public function __construct(ProductTransformer $productTransformer) {
        $this->productTransformer = $productTransformer;
    }

    public function transform(?OrderProduct $orderProduct): array
    {
        if (!$orderProduct) {
            return [];
        }

        return [
            'id' => $orderProduct->id,
            'product_id' => $orderProduct->product_id,
            'currency_name' => $orderProduct->currency_name,
            'unit_price' => $orderProduct->unit_amount,
            'qty' => $orderProduct->qty,
            'product' => $this->productTransformer->transform($orderProduct->product)
        ];
    }
}
