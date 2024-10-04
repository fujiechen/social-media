<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserAgent;

class UserAgentService
{
    public function create(int $userId, string $code)
    {
        $userAgent = $this->getByCode($code);
        if (!is_null($userAgent)) {
            throw new \Exception('Code already existed');
        }

        $user = User::find($userId);
        if(is_null($user)) {
            throw new \Exception('User does not existed');
        }

        $user->assignRole(Role::ROLE_USER_AGENT);

        return UserAgent::create([
            'user_id' => $userId,
            'code' => $code,
        ]);
    }

    public function getByCode(string $code)
    {
        return UserAgent::query()
            ->where('code', '=', $code)
            ->first();
    }

    public function listByUserId(int $userId)
    {
        return UserAgent::query()
            ->where('user_id', '=', $userId)
            ->get();
    }
}
