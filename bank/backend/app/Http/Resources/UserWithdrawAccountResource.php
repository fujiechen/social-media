<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserWithdrawAccountResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'bank_address' => $this->bank_address,
            'comment' => $this->comment,
            'user' => new UserResource($this->user),
        ];
    }
}
