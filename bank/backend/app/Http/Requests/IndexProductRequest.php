<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'currency_id' => 'exists:currencies,id',
            'order_by' => [Rule::in(['id', 'start_amount', 'freeze_days', 'stock'])],
            'sort' => [Rule::in(['desc', 'asc'])],
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
