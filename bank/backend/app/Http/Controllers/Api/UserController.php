<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpsertUserRequest;
use App\Http\Resources\UserAccountResource;
use App\Http\Resources\UserResource;
use App\Models\Currency;
use App\Services\UserAccountService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Api
 */
class UserController extends BaseController
{
    private UserService $userService;
    private UserAccountService $userAccountService;

    public function __construct(UserService $userService, UserAccountService $userAccountService)
    {
        $this->userService = $userService;
        $this->userAccountService = $userAccountService;
    }

    public function show()
    {
        return new UserResource(Auth::user());
    }

    public function accounts(Request $request) {
        $request->validate([
            'currency_id' => 'exists:currencies,id',
            'currency_name' => 'exists:currencies,name'
        ]);

        $currencyId = $request->get('currency_id');
        $currencyName = $request->get('currency_name');

        if ($currencyId) {
            $currencyName = Currency::find($currencyId)->name;
        }

        $query = $this->userAccountService->getUserAccountQuery(Auth::id(), $currencyName);
        $query->orderBy('balance', 'desc');

        return UserAccountResource::collection($query->get());
    }

    public function update(UpsertUserRequest $request)
    {
        $user = Auth::user();

        $email = $request->get('email', null);
        $name = $request->get('name', null);
        $nickname = $request->get('nickname', null);
        $password = $request->get('password', null);
        $phone = $request->get('phone', null);
        $language = $request->get('language', null);
        $whatsapp = $request->get('whatsapp', null);
        $facebook = $request->get('whatsapp', null);
        $telegram = $request->get('telegram', null);

        if (!is_null($password)) {
            $request->validate([
                'password' => 'required|min:8',
                'confirm_password' => 'required|same:password',
                'old_password' => 'required'
            ]);

            $oldPassword = $request->get('old_password');
            if (!Hash::check($oldPassword, $user->password)) {
                throw new IllegalArgumentException("old_password", "Password did not match");
            }
        }

        $user = $this->userService->update(
            $user->id,
            $user->access_token,
            $email,
            $password,
            $name,
            $nickname,
            $language,
            $phone,
            $whatsapp,
            $facebook,
            $telegram
        );
        return new UserResource($user);
    }

    public function support(Request $request) {
        $user = Auth::user();
        $comment = $request->get('comment');
        $this->userService->createSupport($user->id, $comment);
    }

    public function sendResetPasswordEmail(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $this->userService->sendResetPasswordEmail($email);
        return response()->json([]);
    }
}
