<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWithdrawOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|gt:0',
            'user_account_id' => 'required|exists:user_accounts,id',
            'user_withdraw_account_id' => 'required_without:user_address_id|exists:user_withdraw_accounts,id',
            'user_address_id' => 'required_without:user_withdraw_account_id|exists:user_addresses,id',
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
