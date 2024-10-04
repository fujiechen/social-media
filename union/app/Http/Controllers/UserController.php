<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function show(): UserResource {
        return new UserResource(Auth::user());
    }

    public function update(Request $request): UserResource
    {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $validatedData = $request->validate([
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($user),
            ],
            'nickname' => 'nullable',
            'phone' => 'nullable',
            'wechat' => 'nullable',
            'whatsapp' => 'nullable',
            'alipay' => 'nullable',
            'telegram' => 'nullable',
            'facebook' => 'nullable',
            'language' => [
                'nullable',
                Rule::in(Language::LANGUAGES),
            ],
        ]);

        $extras = array_diff($request->all(), $validatedData);
        if (!empty($extras)) {
            $validatedData['extras'] = $extras;
        }

        $user->update($validatedData);
        $user->access_token = JWTAuth::fromUser($user);
        $user->save();

        return new UserResource($user);
    }

    public function updateAuth(Request $request) {
        /**
         * @var User $user
         */
        $user = Auth::user();

        $validatedData = $request->validate([
            'username' => [
                'nullable',
                Rule::unique('users')->ignore($user), // Ignore the current user's username
            ],
            'old_password' => ['required'],
            'password' => ['nullable', 'min:8'], // Allow password to be nullable (not required for updates)
            'confirm_password' => 'nullable|same:password', // Allow confirm_password to be nullable (not required for updates)
        ]);

        if (!Hash::check($validatedData['old_password'], $user->password)) {
            return response()->json(['errors' => ['password' => 'Invalid credentials']], 422);
        }

        if (isset($validatedData['username']) && $user->username != $validatedData['username']) {
            $user->username = $validatedData['username'];
        }

        if (isset($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->access_token = JWTAuth::fromUser($user);
        $user->save();

        return new UserResource($user);
    }

    public function confirmResetPassword(Request $request) {
        $validatedData = $request->validate([
            'access_token' => 'required|exists:users,access_token',
            'password' => ['nullable', 'min:8'], // Allow password to be nullable (not required for updates)
            'confirm_password' => 'nullable|same:password', // Allow confirm_password to be nullable (not required for updates)
        ]);

        /**
         * @var User $user
         */
        $user = User::query()->where('access_token', '=', $validatedData['access_token'])->first();
        $oldToken = $user->access_token;
        $user->password = Hash::make($validatedData['password']);
        $extras = $user->extras;
        $extras['old_token_expired'] = true;
        $user->extras = $extras;
        $user->access_token = JWTAuth::fromUser($user);
        $user->save();

        JWTAuth::setToken($oldToken)->invalidate();

        return new UserResource($user);
    }
}
