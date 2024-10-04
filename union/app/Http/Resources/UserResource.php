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
            'email' => $this->email,
            'name' => $this->nickname,
            'nickname' => $this->nickname,
            'language' => $this->language,
            'phone' => $this->phone,
            'wechat' => $this->wechat,
            'whatsapp' => $this->whatsapp,
            'alipay' => $this->alipay,
            'telegram' => $this->telegram,
            'facebook' => $this->facebook,
            'access_token' => $this->access_token,
            'password' => $this->password,
        ];
    }
}
