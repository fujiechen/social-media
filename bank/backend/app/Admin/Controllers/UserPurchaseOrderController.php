<?php

namespace App\Admin\Controllers;

use App\Models\UserOrder;

class UserPurchaseOrderController extends UserOrderController
{
    protected function getType(): string
    {
        return UserOrder::TYPE_PURCHASE;
    }
}
