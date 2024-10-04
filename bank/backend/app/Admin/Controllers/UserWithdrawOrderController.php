<?php

namespace App\Admin\Controllers;

use App\Models\UserOrder;

class UserWithdrawOrderController extends UserOrderController
{
    protected function getType(): string
    {
        return UserOrder::TYPE_WITHDRAW;
    }
}
