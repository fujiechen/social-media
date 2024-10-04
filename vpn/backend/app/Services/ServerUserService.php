<?php

namespace App\Services;

use App\Dtos\ServerUserDto;
use App\Models\ServerUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ServerUserService
{
    public function fetchServerUsersQuery(?int $userId = null, ?int $categoryId = null): Builder {
        $query = ServerUser::query()->select('server_users.*');

        if ($categoryId) {
            $query->join('servers', 'servers.id', '=', 'server_users.server_id')
                ->where('category_id', '=', $categoryId);
        }

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query;
    }

    public function updateOrCreateServerUser(ServerUserDto $dto): ServerUser {
        return DB::transaction(function() use ($dto) {

            /**
             * @var ServerUser $serverUser
             */
            $serverUser = ServerUser::query()->updateOrCreate([
                'server_id' => $dto->serverId,
                'user_id' => $dto->userId,
            ], [
                'server_id' => $dto->serverId,
                'user_id' => $dto->userId,
                'radius_uuid' => $dto->radiusUuid,
                'radius_username' => $dto->radiusUsername,
                'radius_password' => $dto->radiusPassword,
            ]);

            return $serverUser;
        });
    }

    public function findServerUser(int $serverId, int $userId): ?ServerUser {
        return ServerUser::where('user_id', $userId)->where('server_id', $serverId)->first();
    }

    /**
     * Has to start with letter
     * @param int $serverId
     * @param int $userId
     * @return string
     */
    public function generateRadiusUsername(int $categoryId, int $serverId, int $userId): string {
        return 'c' . $categoryId . 's' . $serverId . 'u' . $userId;
    }

    public function generateRadiusPassword(): string {
        return (string)rand(100000, 999999);
    }
}
