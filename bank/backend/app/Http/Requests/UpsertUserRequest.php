<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'email' => ['email', Rule::unique('users')->ignore($this->user()->id)],
            'password' => 'min:8',
            'phone' => 'min:8',
            'language' => Rule::in(Language::LANGUAGES),
            'confirmPassword' => 'same:password',
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
