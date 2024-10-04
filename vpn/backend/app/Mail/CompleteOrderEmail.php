<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompleteOrderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build() {
        return $this->markdown('emails.order.complete')
            ->subject('您的' . config('app.name') . '订单 - ' . $this->order->id)
            ->with([
                'user' => $this->order->user->nickname,
                'orderId' => $this->order->id,
                'orderDate' => $this->order->created_at_formatted,
                'orderUrl' => config('app.frontend_url') . '/order/' . $this->order->id
            ]);
    }

}
