<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Cache\UserCacheService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserRoleService
{
    private UserCacheService $userCacheService;

    public function __construct(UserCacheService $userCacheService) {
        $this->userCacheService = $userCacheService;
    }

    public function updateOrCreateUserRole(int $userId, int $roleId, ?int $extendDays = null): UserRole {
        return DB::transaction(function() use ($userId, $roleId, $extendDays) {
            /**
             * @var UserRole $userRole
             */
            $userRole = UserRole::updateOrCreate([
                'user_id' => $userId,
                'role_id' => $roleId,
            ],[
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            if ($roleId == Role::ROLE_MEMBERSHIP_ID || $roleId == Role::ROLE_AGENT_ID) {
                if ($extendDays != null) {
                    if ($userRole->valid_until_at == null) {
                        $validUtilAt = Carbon::now();
                    } else {
                        $validUtilAt = $userRole->valid_until_at;
                    }
                    $validUtilAt->addDays($extendDays);
                    $userRole->valid_until_at = $validUtilAt;
                } else {
                    $userRole->valid_until_at = null;
                }
                $userRole->save();
            }

            $this->userCacheService->resetAndCreateUserProfile($userId);
            return $userRole;
        });
    }

    public function fetchExpiredUserRoleUsers(int $roleId): Builder
    {
        $now = Carbon::now();
        return User::query()
            ->select('users.*')
            ->distinct()
            ->join('user_role_users', 'user_role_users.user_id', '=', 'users.id')
            ->where('user_role_users.role_id', '=', $roleId)
            ->where('user_role_users.valid_until_at', '<', $now);
    }
}
