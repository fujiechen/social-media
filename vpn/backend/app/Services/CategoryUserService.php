<?php

namespace App\Services;

use App\Dtos\CategoryUserDto;
use App\Models\CategoryUser;
use Illuminate\Support\Facades\DB;

class CategoryUserService
{
    public function findCategoryUserByIp(int $userId, string $ip): ?CategoryUser {
        return CategoryUser::select('category_users.*')
            ->join('servers', 'category_users.category_id', '=', 'servers.category_id')
            ->where('servers.ip', '=', $ip)
            ->where('category_users.user_id', '=', $userId)
            ->first();
    }

    public function findCategoryUser(int $categoryId, int $userId): ?CategoryUser {
        return CategoryUser::where('category_id', '=', $categoryId)
            ->where('user_id', '=', $userId)
            ->first();
    }

    public function updateOrCreateCategoryUser(CategoryUserDto $dto): CategoryUser {
        return DB::transaction(function() use ($dto) {

            /**
             * @var CategoryUser $categoryUser
             */
            $categoryUser = CategoryUser::query()->updateOrCreate([
                'category_id' => $dto->categoryId,
                'user_id' => $dto->userId,
            ], [
                'category_id' => $dto->categoryId,
                'user_id' => $dto->userId,
                'valid_until_at' => $dto->validUntilAt,
            ]);

            return $categoryUser;
        });
    }
}
