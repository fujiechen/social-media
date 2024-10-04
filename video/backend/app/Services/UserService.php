<?php

namespace App\Services;

use App\Dtos\UserDto;
use App\Dtos\UserShareDto;
use App\Models\MediaHistory;
use App\Models\Role;
use App\Models\User;
use App\Models\UserReferral;
use App\Models\UserRole;
use App\Models\UserShare;
use App\Services\Cache\UserCacheService;
use Illuminate\Database\Eloquent\Builder;

class UserService
{
    private UnionGatewayService $unionGatewayService;
    private UserCacheService $userCacheService;

    public function __construct(UnionGatewayService $unionGatewayService, UserCacheService $userCacheService) {
        $this->unionGatewayService = $unionGatewayService;
        $this->userCacheService = $userCacheService;
    }

    /**
     * Used by Admin Portal Only
     * @param UserDto $userDto
     * @return User
     */
    public function createUnionUser(UserDto $userDto): User {
        $response = $this->unionGatewayService->createUser($userDto);
        $data = $response->json('data');
        $userDto->userId = $data['id'];
        $userDto->accessToken = $data['access_token'];
        return $this->createUser($userDto);
    }

    public function createUser(UserDto $userDto): User
    {
        /**
         * @var User $user
         */
        $user = User::query()->create([
            'id' => $userDto->userId,
            'username' => $userDto->username,
            'password' => $userDto->password,
            'nickname' => $userDto->nickname,
            'access_token' => $userDto->accessToken,
            'email' => $userDto->email,
            'user_share_id' => $userDto->userShareId,
        ]);

        // new registration user give two roles visitor and user
        if (empty($userDto->roleIds)) {
            UserRole::query()->updateOrCreate([
                'user_id' => $user->id,
                'role_id' => Role::ROLE_VISITOR_ID,
            ]);

            UserRole::query()->updateOrCreate([
                'user_id' => $user->id,
                'role_id' => Role::ROLE_USER_ID,
            ]);
        } else {
            foreach ($userDto->roleIds as $roleId) {
                UserRole::query()->updateOrCreate([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ]);
            }
        }


        if (UserShare::find($userDto->userShareId)) {
            $this->createUserReferrals($userDto->userShareId, $user->id);
        }

        return $user;
    }

    public function createUserReferrals(int $userShareId, int $newUserId): void
    {
        $userShare = UserShare::find($userShareId);
        $parentUserId = $userShare->user_id;

        //add child user to parent user
        UserReferral::create([
            'user_id' => $parentUserId,
            'sub_user_id' => $newUserId,
            'level' => 1,
            'user_share_id' => $userShareId,
        ]);

        //all grand-parent users add child user
        $parentParentUserReferrals = UserReferral::query()
            ->where('sub_user_id', '=', $parentUserId)
            ->get();

        foreach ($parentParentUserReferrals as $parentParentUserReferral) {
            UserReferral::create([
                'user_id' => $parentParentUserReferral->user_id,
                'sub_user_id' => $newUserId,
                'level' => $parentParentUserReferral->level + 1,
                'user_share_id' => $userShareId,
            ]);
        }
    }


    /**
     * Add user role never delete role
     * @param UserDto $userDto
     * @return User
     */
    public function updateUserAuth(UserDto $userDto): User
    {
        /**
         * @var User $user
         */
        $user = User::query()->find($userDto->userId);
        $user->username = $userDto->username;
        $user->email = $userDto->email;
        $user->password = $userDto->password;
        $user->nickname = $userDto->nickname;
        $user->access_token = $userDto->accessToken;
        $user->save();

        foreach ($userDto->roleIds as $roleId) {
            UserRole::query()->updateOrCreate([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ], [
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        $this->userCacheService->resetAndCreateUserProfile($user->id);

        return $user;
    }

    public function findSubUsersQuery(int $userId, array $roleIds = []): Builder
    {
        $query = UserReferral::query()
            ->where('user_referrals.user_id', '=', $userId);

        if (!empty($roleIds)) {
            $query->join('user_role_users', 'sub_user_id', '=', 'user_role_users.user_id');
            $query->whereIn('role_id', $roleIds);
        }

        return $query;
    }


    public function createUserShare(UserShareDto $userShareDto): UserShare
    {
        return UserShare::create([
            'user_id' => $userShareDto->userId,
            'shareable_type' => $userShareDto->shareableType ?? null,
            'shareable_id' => $userShareDto->shareableId ?? null,
            'url' => $userShareDto->url,
        ]);
    }

    public function createUserHistory(int $userId, int $mediaId): MediaHistory
    {
        return MediaHistory::create([
            'user_id' => $userId,
            'media_id' => $mediaId,
        ]);
    }

    public function findUserSharesQuery(int $userId): Builder
    {
        return UserShare::query()->where('user_id', '=', $userId);
    }

    public function getUserAndIncreaseCount(int $userId): User {
        /**
         * @var User $user
         */
        $user = User::find($userId);

        User::withoutEvents(function() use ($user) {
            $user->views_count++;
            $user->save();
        });

        return $user;
    }
}
