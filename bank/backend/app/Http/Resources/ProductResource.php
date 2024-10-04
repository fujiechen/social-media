<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'title' => $this->title,
            'name' => $this->name,
            'estimate_rate' => number_format($this->estimate_rate / 100, 2, '.', '') . '%',
            'trend' => $this->trend,
            'description' => $this->description,
            'start_amount' => Formatter::formatAmount($this->start_amount, $this->currency->symbol, 0),
            'freeze_days' => $this->freeze_days,
            'is_recommend' => $this->is_recommend,
            'stock' => $this->stock,
            'fund_fact_url' => $this->fund_fact_url,
            'prospectus_url' => $this->prospectus_url,
            'fund_assets' => $this->fund_assets,
            'currency' => $this->currency,
            'category' => $this->productCategory,
            'product_rates' => ProductRateResource::collection($this->recent_product_rates),
        ];
    }
}
