<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'username' => $this->username,
            'name' => $this->nickname,
            'email' => $this->email,
            'phone' => $this->phone,
            'language' => $this->language,
            'whatsapp' => $this->whatsapp,
            'facebook' => $this->facebook,
            'telegram' => $this->telegram,
            'wechat' => $this->wechat,
            'alipay' => $this->alipay,
            'user_accounts' => UserAccountResource::collection($this->userAccounts),
        ];
    }
}
