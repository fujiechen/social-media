<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_active' => $this->is_active,
            'total_market_value' => Formatter::formatAmount($this->total_market_value, $this->userOrder->product->currency->symbol),
            'total_book_cost' => Formatter::formatAmount($this->total_book_cost, $this->userOrder->product->currency->symbol),
            'total_increase_rate' => Formatter::formatPercentage($this->total_book_cost, $this->total_market_value),
            'final_market_value' => Formatter::formatAmount($this->final_market_value, $this->userOrder->product->currency->symbol),
            'final_book_cost' => Formatter::formatAmount($this->final_book_cost, $this->userOrder->product->currency->symbol),
            'final_increase_rate' => Formatter::formatPercentage($this->final_book_cost, $this->final_market_value),
            'trend' => $this->is_active
                ? Formatter::formatTrend($this->total_book_cost, $this->total_market_value)
                : Formatter::formatTrend($this->final_book_cost, $this->final_market_value),
            'user_order' => new UserOrderResource($this->userOrder),
        ];
    }
}
