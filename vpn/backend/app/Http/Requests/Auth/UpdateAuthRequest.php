<?php

namespace App\Http\Requests\Auth;

use App\Dtos\UserDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAuthRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'min:3',
                Rule::unique('users')->ignore(Auth::user()->username, 'username'),
            ],
            'password' => ['required', 'min:8'],
            'confirm_password' => 'required|same:password',
            'email' => 'required|email',
            'nickname' => 'required',
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return true;
    }

    function toDto(): UserDto
    {
        return new UserDto([
            'userId' => Auth::user()->id,
            'username' => $this->input('username'),
            'password' => $this->input('password'),
            'email' => $this->input('email'),
            'nickname' => $this->input('nickname'),
            'roleIds' => [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID],
        ]);
    }
}
