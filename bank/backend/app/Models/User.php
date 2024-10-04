<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;


/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $nickname
 * @property string $password
 * @property string $language
 * @property string $phone
 * @property string $wechat
 * @property string $alipay
 * @property string $whatsapp
 * @property string $facebook
 * @property string $telegram
 * @property ?int $user_agent_id
 * @property ?string $access_token
 * @property-read Collection $userAccounts
 * @property-read ?UserAgent $userAgent
 */
class User extends Administrator
{
    protected $attributes = [
        'language' => 'en',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'username',
        'access_token',
        'email',
        'nickname',
        'password',
        'language',
        'phone',
        'alipay',
        'wechat',
        'whatsapp',
        'facebook',
        'telegram',
        'user_agent_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected $appends = [
        'role_names',
    ];

    public function userAgent(): BelongsTo
    {
        return $this->belongsTo(UserAgent::class);
    }

    public function userAccounts(): HasMany
    {
        return $this->hasMany(UserAccount::class);
    }

    public function getRoleNamesAttribute(): Collection {
        return $this->roles->pluck('name');
    }

    public function hasRoleId(int $roleId): bool {
        return $this->inRoles($roleId);
    }
}
