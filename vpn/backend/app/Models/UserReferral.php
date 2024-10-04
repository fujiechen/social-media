<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property User $user
 * @property int $sub_user_id
 * @property User $subUser
 * @property int $level
 * @property UserShare $userShare
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 */
class UserReferral extends Model
{
    use HasUser;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $table = 'user_referrals';

    protected $fillable = [
        'user_id',
        'sub_user_id',
        'level',
        'user_share_id',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'subUser',
        'userShare',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function subUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sub_user_id');
    }

    public function userShare(): BelongsTo
    {
        return $this->belongsTo(UserShare::class, 'user_share_id');
    }

}
