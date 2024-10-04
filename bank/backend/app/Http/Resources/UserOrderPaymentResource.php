<?php

namespace App\Http\Resources;

use App\Utils\Formatter;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'status' => $this->status,
            'amount' => $this->amount,
            'amount_in_dollar' => $this->amount_in_dollar,
            'stripe_intent_client_secret' => $this->stripe_intent_client_secret,
            'payment_gateway' => $this->paymentGateway ? new PaymentGatewayResource($this->paymentGateway) : null,
            'response' => $this->response,
            'created_at' => $this->created_at_formatted,
        ];
    }
}
