<?php

namespace App\Http\Requests;

use App\Http\Rules\IsSameUserActionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IndexUserProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'is_active' => 'boolean',
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
