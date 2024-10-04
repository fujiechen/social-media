<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransferOrderRequest extends FormRequest
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
            'from_currency_name' => 'required_without:from_user_account_id|exists:currencies,name',
            'from_user_account_id' => 'required_without:from_currency_name|exists:user_accounts,id',
            'to_user_email' => 'required',
            'to_user_name' => 'required',
            'to_user_access_token' => 'nullable',
            'comment' => 'nullable',
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
