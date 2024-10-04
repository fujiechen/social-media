<?php

namespace App\Transformers;

use App\Models\UserPayout;
use App\Utils\Utilities;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class UserPayoutTransformer extends TransformerAbstract
{
    public function transform(UserPayout $userPayout): array
    {
        return [
            'id' => $userPayout->id,
            'order_user_nickname' => Str::mask($userPayout->order_user_nickname, '*', 1),
            'status' => $userPayout->status,
            'status_name' => $userPayout->status_name,
            'type' => $userPayout->type,
            'currency_name' => $userPayout->currency_name,
            'amount' => $userPayout->amount,
            'comment' => $userPayout->comment,
            'amount_formatted' => Utilities::formatCurrency($userPayout->currency_name, $userPayout->amount_cents),
            'created_at_formatted' => $userPayout->created_at_formatted,
        ];
    }
}
