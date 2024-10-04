<?php

namespace App\Admin\Controllers;

use App\Models\UserOrder;

class UserExchangeOrderController extends UserOrderController
{
    protected function getType(): string
    {
        return UserOrder::TYPE_EXCHANGE;
    }
}
