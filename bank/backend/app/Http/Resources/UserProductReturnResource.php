<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'market_value' => Formatter::formatAmount($this->market_value, $this->userProduct->userOrder->product->currency->symbol),
            'book_cost' => Formatter::formatAmount($this->book_cost, $this->userProduct->userOrder->product->currency->symbol),
            'comment' => $this->comment,
            'product_rate' => new ProductRateResource($this->productRate),
            'user_product' => new UserProductResource($this->userProduct),
            'created_at' => Formatter::formatDateFromString($this->created_at),
        ];
    }
}
