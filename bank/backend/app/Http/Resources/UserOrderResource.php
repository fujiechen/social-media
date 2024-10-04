<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $toUser = [];
        if ($this->toUserAccount) {
            $toUser = [
                'name' => $this->toUserAccount->user->nickname,
                'email' => $this->toUserAccount->user->email,
                'account_number' => $this->toUserAccount->account_number,
            ];
        }
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'amount' => Formatter::formatAmount($this->amount, $this->userAccount->currency->symbol),
            'comment' => $this->comment,
            'meta_json' => $this->meta_json,
            'release_at' => Formatter::formatDate($this->getReleaseDate()),
            'created_at' => Formatter::formatDateTimeFromString($this->created_at),
            'product' => new ProductResource($this->product),
            'user_account' => new UserAccountResource($this->userAccount),
            'to_user' => $toUser,
            'user_transactions' => $this->whenLoaded('userTransactions', UserTransactionResource::collection($this->userTransactions)),
            'user_order_payments' => $this->whenLoaded('userOrderPayments', UserOrderPaymentResource::collection($this->userOrderPayments)),
        ];
    }
}
