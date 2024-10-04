<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'currency_id' => 'required|exists:currencies,id',
            'product_category_id' => 'required|exists:product_categories,id',
            'title' => 'required',
            'name' => 'required',
            'description' => 'required',
            'start_amount' => 'required|numeric|gt:0',
            'stock' => 'required|numeric|gt:0',
            'freeze_days' => 'required|numeric|gt:0',
            'is_recommend' => 'required',
            'estimate_rate' => 'required|numeric|gt:0'
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
