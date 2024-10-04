<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDepositOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'user_account_id' => 'required_without:currency_name|exists:user_accounts,id',
            'currency_name' => 'required_without:user_account_id|exists:currencies,name',
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'required',
            'callback_url' => 'string|url',
            'meta_json' => 'nullable',
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
