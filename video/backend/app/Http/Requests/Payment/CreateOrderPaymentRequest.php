<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderPaymentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        $this->merge(['order_id' => $this->route('orderId')]);
        return [
            'order_id' => ['required', 'exists:orders,id',
                Rule::exists('orders', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id());
                })]
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return true;
    }
}
