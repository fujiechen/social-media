<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePurchaseOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required||exists:products,id',
            'amount' => 'required|numeric|gt:0',
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
