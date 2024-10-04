<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $nickname
 * @property string $email
 * @property string $language
 * @property string $phone
 * @property string $wechat
 * @property string $whatsapp
 * @property string $alipay
 * @property string $telegram
 * @property string $facebook
 * @property string $access_token
 * @property array $extras
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'access_token',
        'username',
        'password',
        'nickname',
        'email',
        'language',
        'phone',
        'whatsapp',
        'wechat',
        'alipay',
        'telegram',
        'facebook',
        'extras',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'extras' => 'array',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'username' => $this->username,
                'password' => $this->password,
                'email' => $this->email,
                'nickname' => $this->nickname,
                'language' => $this->language,
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'telegram' => $this->telegram,
                'facebook' => $this->facebook,
                'alipay' => $this->alipay,
                'wechat' => $this->wechat,
                'extras' => $this->extras,
            ],
        ];
    }
}
