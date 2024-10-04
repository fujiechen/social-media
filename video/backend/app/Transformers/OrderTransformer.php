<?php

namespace App\Transformers;

use App\Models\Order;
use App\Utils\Utilities;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class OrderTransformer extends TransformerAbstract
{
    private OrderProductTransformer $orderProductTransformer;
    private OrderPaymentTransformer $orderPaymentTransformer;

    public function __construct(OrderProductTransformer $orderProductTransformer, OrderPaymentTransformer $orderPaymentTransformer) {
        $this->orderProductTransformer = $orderProductTransformer;
        $this->orderPaymentTransformer = $orderPaymentTransformer;
    }

    public function transform(?Order $order): array
    {
        if (!$order) {
            return [];
        }

        $includes = [];
        if ($this->getCurrentScope()) {
            $includes = $this->getCurrentScope()->getManager()->getRequestedIncludes();
        }

        $orderProducts = [];
        foreach ($order->orderProducts as $orderProduct) {
            $orderProducts[] = $this->orderProductTransformer->transform($orderProduct);
        }

        $data = [
            'id' => $order->id,
            'user_nickname' => Str::mask($order->user->nickname, '*', 1),
            'user_id' => $order->user_id,
            'status' => $order->status,
            'status_name' => $order->status_name,
            'total_amount' => $order->total_amount,
            'currency_name' => $order->currency_name,
            'amount_formatted' => Utilities::formatCurrency($order->currency_name, $order->total_cents),
            'created_at_formatted' => $order->created_at_formatted,
            'updated_at_formatted' => $order->updated_at_formatted,
            'order_products' => $orderProducts,
        ];

        if (in_array('payments', $includes)) {
            $data['payments'] = [];
            foreach ($order->payments as $orderPayment) {
                $data['payments'][] = $this->orderPaymentTransformer->transform($orderPayment);
            }
        }

        return $data;
    }
}
