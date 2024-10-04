<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $union_user_id
 * @property string $username
 * @property string $password
 * @property string $nickname
 * @property string $email
 * @property string $access_token
 * @property string $phone
 * @property File $avatarFile
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection $userRoles
 * @property Collection|User[] $followerUsers
 * @property Collection|User[] $publisherUsers
 * @property HasMany $medias
 * @property Collection $userFollowingOfFollowers
 * @property Collection $userFollowingOfPublishers
 * @property int $views_count
 * @property int $priority
 * @property array $role_ids
 * @property array $role_names
 */
class User extends Administrator
{
    protected $table = 'users';

    protected $fillable = [
        'id',
        'email',
        'username',
        'access_token',
        'password',
        'nickname',
        'phone',
        'avatar_file_id',
        'user_share_id',
        'views_count',
        'priority',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'role_names',
        'role_ids',
    ];

    protected $with = [
        'userRoles'
    ];

    public function hasRole(int $roleId): bool {
        $userRole = $this->getRole($roleId);

        if (!$userRole) {
            return false;
        }

        if (!$userRole->valid_until_at) {
            return true;
        }

        return $userRole->valid_until_at_days > 0;
    }

    public function getRole(int $roleId): ?UserRole {
        return $this->userRoles()->where('role_id', '=', $roleId)->first();
    }

    public function userRoles(): HasMany {
        return $this->hasMany(UserRole::class);
    }

    public function subscriptionProducts(): HasMany {
        return $this->hasMany(Product::class, 'publisher_user_id');
    }

    public function getRoleNamesAttribute(): array {
        $names = [];
        foreach ($this->roles as $role) {
            if ($this->hasRole($role->id)) {
                $names[] = $role->name;
            }
        }
        return $names;
    }

    public function getRoleIdsAttribute(): array {
        $roleIds = [];
        foreach ($this->userRoles as $userRole) {
            if ($this->hasRole($userRole->role_id)) {
                $roleIds[] = $userRole->role_id;
            }
        }
        return $roleIds;
    }

    public function followerUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, UserFollowing::class,
            'publisher_user_id', 'id', 'id', 'following_user_id')
            ->where(function ($query) {
                $query->whereNull('valid_until_at')
                    ->orWhere('valid_until_at', '>', Carbon::now());
            });
    }

    public function publisherUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, UserFollowing::class,
            'following_user_id', 'id', 'id', 'publisher_user_id')
            ->where(function ($query) {
                $query->whereNull('valid_until_at')
                    ->orWhere('valid_until_at', '>', Carbon::now());
            });
    }

    public function userFollowingOfFollowers(): HasMany {
        return $this->hasMany(UserFollowing::class, 'publisher_user_id');
    }

    public function userFollowingOfPublishers(): HasMany {
        return $this->hasMany(UserFollowing::class, 'following_user_id');
    }

    public function totalSubscriptions(): int {
        return $this->userFollowingOfPublishers()->where(function ($query) {
            $query->whereNull('valid_until_at')
                ->orWhere('valid_until_at', '>', Carbon::now());
        })->count();
    }

    public function totalFollowerUsers(): int {
        return $this->userFollowingOfFollowers()
            ->where(function ($query) {
                $query->whereNull('valid_until_at')
                    ->orWhere('valid_until_at', '>', Carbon::now());
            })
            ->count();
    }

    public function avatarFile(): BelongsTo {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    public function medias(): HasMany {
        return $this->hasMany(Media::class);
    }

    public function totalMediaVideos(): int {
        return $this->medias->where('status', '=', Media::STATUS_ACTIVE)->where('type', '=', Media::TYPE_VIDEO)->count();
    }

    public function totalMediaSeries(): int {
        return $this->medias->where('status', '=', Media::STATUS_ACTIVE)->where('type', '=', Media::TYPE_SERIES)->count();
    }

    public function totalMediaAlbums(): int {
        return $this->medias->where('status', '=', Media::STATUS_ACTIVE)->where('type', '=', Media::TYPE_ALBUM)->count();
    }
}
