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
 * @property Collection|User[] $subscriptionUsers
 * @property Collection|User[] $subscriberUsers
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
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'role_names',
        'role_ids',
    ];

    public function hasRole(int $roleId) {
        return $this->inRoles($roleId);
    }

    public function subscriptionProducts(): HasMany {
        return $this->hasMany(Product::class, 'publisher_user_id');
    }

    public function getRoleNamesAttribute(): array {
        $names = [];
        foreach ($this->roles as $role) {
            $names[] = $role->name;
        }
        return $names;
    }

    public function getRoleIdsAttribute(): array {
        $names = [];
        foreach ($this->roles as $role) {
            $names[] = $role->id;
        }
        return $names;
    }

    public function userRoles(): HasMany {
        return $this->hasMany(UserRole::class);
    }

    public function subscriberUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, UserSubscriber::class,
            'user_id', 'id', 'id', 'subscriber_user_id');
    }

    public function subscriptionUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, UserSubscriber::class,
            'subscriber_user_id', 'id', 'id', 'user_id');
    }

    public function isRoleVisitor(): bool {
        return $this->userRoles->count() == 1 && $this->getRoleIdsAttribute()[0] == Role::ROLE_VISITOR_ID;
    }

    public function avatarFile(): BelongsTo {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

}
