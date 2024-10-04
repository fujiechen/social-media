<?php

namespace App\Services;

use App\Models\Role;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserRoleService
{
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
                    if ($userRole->valid_util_at == null) {
                        $validUtilAt = Carbon::now();
                    } else {
                        $validUtilAt = $userRole->valid_util_at;
                    }
                    $validUtilAt->addDays($extendDays);
                    $userRole->valid_util_at = $validUtilAt;
                } else {
                    $userRole->valid_util_at = null;
                }
                $userRole->save();
            }


            return $userRole;
        });
    }
}
