<?php

namespace App\Admin\Controllers;

use App\Models\UserOrder;

class UserDepositOrderController extends UserOrderController
{
    protected function getType(): string
    {
        return UserOrder::TYPE_DEPOSIT;
    }
}
