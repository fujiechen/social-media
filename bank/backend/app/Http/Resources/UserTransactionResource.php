<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTransactionResource extends JsonResource
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
            'type' => $this->type,
            'amount' => Formatter::formatAmount($this->amount, $this->userAccount->currency->symbol),
            'balance' => Formatter::formatAmount($this->balance, $this->userAccount->currency->symbol),
            'status' => $this->status,
            'comment' => $this->comment,
            'created_at' => Formatter::formatDateTimeFromString($this->created_at),
            'user_account' => new UserAccountResource($this->userAccount),
        ];
    }
}
