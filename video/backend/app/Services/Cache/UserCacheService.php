<?php

namespace App\Services\Cache;

use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Item;

class UserCacheService
{
    private string $prefix = 'user_';
    private Fractal $fractal;
    private UserTransformer $userTransformer;

    public function __construct(Fractal $fractal, UserTransformer $userTransformer) {
        $this->fractal = $fractal;
        $this->userTransformer = $userTransformer;
    }

    public function getOrCreateUserProfile(int $userId): array {
        $user = User::find($userId);
        $includes = ['email', 'roles', 'access_token', 'roles'];
        $key = $this->prefix . $user->id . '_includes_' . implode('_', $includes);
        return Cache::rememberForever($key, function() use ($user, $includes) {
            $resource = new Item($user, $this->userTransformer);
            $this->fractal->parseIncludes($includes);
            return $this->fractal->createData($resource)->toArray();
        });
    }

    public function resetAndCreateUserProfile($userId): array {
        $includes = ['email', 'roles', 'access_token', 'roles'];
        $key = $this->prefix . $userId . '_includes_' . implode('_', $includes);
        Cache::forget($key);
        return $this->getOrCreateUserProfile($userId);
    }
}
