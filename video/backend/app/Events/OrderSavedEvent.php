<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderSavedEvent
{
    use SerializesModels;

    public Order $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }
}
