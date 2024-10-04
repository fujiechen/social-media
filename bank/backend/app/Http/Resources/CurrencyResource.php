<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'is_default' => $this->is_default,
            'purchase_enabled' => $this->purchase_enabled,
            'deposit_enabled' => $this->deposit_enabled,
            'withdraw_enabled' => $this->withdraw_enabled,
            'exchange_enabled' => $this->exchange_enabled,
            'transfer_enabled' => $this->transfer_enabled,
        ];
    }
}
