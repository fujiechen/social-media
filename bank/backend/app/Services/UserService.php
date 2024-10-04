<?php

namespace App\Services;

use App\Mail\ResetPasswordEmail;
use App\Models\Currency;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserSupport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserService
{
    private UserAccountService $userAccountService;

    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    public function create(?int $userId, ?string $accessToken, string $nickname, string $email, string $password, string $username, string $language,
                           array $roleIds, ?int $userAgentId = null, ?string $phone = null, ?string $wechat = null, ?string $whatsapp = null,
                           ?string $alipay = null, ?string $telegram = null, ?string $facebook = null)
    {
        $user = User::create([
            'id' => $userId,
            'nickname' => $nickname,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'language' => $language,
            'user_agent_id' => $userAgentId,
            'access_token' => $accessToken,
            'phone' => $phone,
            'wechat' => $wechat,
            'alipay' => $alipay,
            'whatsapp' => $whatsapp,
            'telegram' => $telegram,
            'facebook' => $facebook,
        ]);

        if (empty($roleIds)) {
            UserRole::query()->updateOrCreate([
                'user_id' => $user->id,
                'role_id' => Role::ROLE_USER_ID,
            ], [
                'user_id' => $user->id,
                'role_id' => Role::ROLE_USER_ID,
            ]);
        } else {
            foreach ($roleIds as $roleId) {
                UserRole::query()->updateOrCreate([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ], [
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ]);
            }
        }

        foreach (Currency::all() as $currency) {
            $this->userAccountService->create($user->id, $currency->id);
        }

        return $user;
    }

    public function update(
        int     $userId,
        string $accessToken,
        ?string $email = null,
        ?string $password = null,
        ?string $username = null,
        ?string $nickname = null,
        ?string $language = null,
        ?string $phone = null,
        ?string $whatsapp = null,
        ?string $facebook = null,
        ?string $telegram = null,
        ?string $wechat = null,
        ?string $alipay = null,
    )
    {
        /**
         * @var User $user
         */
        $user = User::find($userId);

        $user->access_token = $accessToken;

        if (!is_null($email)) {
            $user->email = $email;
        }
        if (!is_null($nickname)) {
            $user->nickname = $nickname;
        }
        if (!is_null($password)) {
            $user->password = Hash::make($password);
        }
        if (!is_null($username)) {
            $user->username = $username;
        }
        if (!is_null($language)) {
            $user->language = $language;
        }
        if (!is_null($phone)) {
            $user->phone = $phone;
        }
        if (!is_null($whatsapp)) {
            $user->whatsapp = $whatsapp;
        }
        if (!is_null($facebook)) {
            $user->facebook = $facebook;
        }
        if (!is_null($telegram)) {
            $user->telegram = $telegram;
        }
        if (!is_null($wechat)) {
            $user->wechat = $wechat;
        }
        if (!is_null($alipay)) {
            $user->alipay = $alipay;
        }

        $user->save();
        return $user;
    }

    public function createSupport(int $userId, string $comment)
    {
        return UserSupport::create([
            'user_id' => $userId,
            'comment' => $comment,
        ]);
    }

    public function sendResetPasswordEmail(string $email): void {
        /**
         * @var User $user
         */
        $user = User::query()->where('email', '=', $email)->first();
        $resetUrl = env('FRONTEND_RESET_PASSWORD_URL') . '/' . $user->access_token;
        Mail::to($email)->queue(new ResetPasswordEmail($user, $resetUrl));
    }
}
