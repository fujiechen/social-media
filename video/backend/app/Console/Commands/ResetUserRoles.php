<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Cache\UserCacheService;
use App\Services\UserRoleService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:reset-user-roles';

    protected $description = 'Reset User Roles';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info('start running reset user roles ');
        /**
         * @var UserRoleService $userRoleService
         */
        $userRoleService = app(UserRoleService::class);
        $userRoleService->fetchExpiredUserRoleUsers(Role::ROLE_MEMBERSHIP_ID)
            ->chunk(100, function ($users) {
                foreach ($users as $user) {
                    Log::info('delete expired membership of user ' . $user->id);
                    UserRole::query()->where('user_id', '=', $user->id)
                        ->where('role_id', '=', Role::ROLE_MEMBERSHIP_ID)
                        ->delete();

                    /**
                     * @var UserCacheService $userCacheService
                     */
                    $userCacheService = app(UserCacheService::class);
                    $userCacheService->resetAndCreateUserProfile($user->id);
                }
            });
        Log::info('completed running reset user roles ');
    }
}
