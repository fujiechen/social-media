<?php

namespace App\Http\Requests;

use App\Models\UserOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'user_account_id' => 'exists:user_accounts,id',
            'order_type' => [Rule::in(UserOrder::TYPES)],
            'product_id' => 'exists:products,id',
            'limit' => 'numeric|gt:0',
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
