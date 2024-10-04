<?php

namespace App\Transformers;

use App\Models\Payment;
use League\Fractal\TransformerAbstract;

class OrderPaymentTransformer extends TransformerAbstract
{
    public function transform(?Payment $orderPayment): array
    {
        if (!$orderPayment) {
            return [];
        }

        return [
            'id' => $orderPayment->id,
            'order_id' => $orderPayment->order->id,
            'status' => $orderPayment->status,
            'currency_name' => $orderPayment->currency_name,
            'amount' => $orderPayment->amount,
            'created_at_formatted' => $orderPayment->created_at_formatted
        ];
    }
}
