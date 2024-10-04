<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAccountResource extends JsonResource
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
            'account_number' => $this->account_number,
            'balance' => Formatter::formatAmount($this->balance, $this->currency->symbol),
            'product_balance' => Formatter::formatAmount($this->product_balance, $this->currency->symbol),
            'assets' => Formatter::formatAmount($this->getAssets(), $this->currency->symbol),
            'currency' => new CurrencyResource($this->currency)
        ];
    }
}
