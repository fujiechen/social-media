<?php

namespace App\Http\Requests\Auth;

use App\Dtos\UserDto;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|unique:users',
            'username' => 'required|unique:users',
            'email' => 'required|email',
            'nickname' => 'required',
            'password' => ['required', 'min:8'],
            'confirm_password' => 'required|same:password',
            'user_share_id' => 'nullable|exists:user_shares,id',
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
            'userId' => $this->input('id'),
            'username' => $this->input('username'),
            'password' => $this->input('password'),
            'email' => $this->input('email'),
            'nickname' => $this->input('nickname'),
            'userShareId' => $this->input('user_share_id'),
            'roleIds' => [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID],
        ]);
    }
}
