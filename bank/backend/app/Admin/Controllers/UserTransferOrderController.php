<?php

namespace App\Admin\Controllers;

use App\Models\UserOrder;

class UserTransferOrderController extends UserOrderController
{
    protected function getType(): string
    {
        return UserOrder::TYPE_TRANSFER;
    }
}
