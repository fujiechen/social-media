<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $role_id
 * @property ?Carbon $valid_util_at
 * @property ?string $valid_util_at_formatted
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $user
 * @property Role $role
 */
class UserRole extends Model
{
    public $table = 'user_role_users';

    use HasUser;

    protected $fillable = [
        'user_id',
        'role_id',
        'valid_util_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'valid_util_at' => 'datetime',
    ];

    protected $appends = [
        'valid_util_at_formatted',
    ];

    protected function setKeysForSaveQuery($query): Builder {
        $query
            ->where('user_id', '=', $this->getAttribute('user_id'))
            ->where('role_id', '=', $this->getAttribute('role_id'));

        return $query;
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }

    public function getValidUtilAtFormattedAttribute(): ?string {
        return $this->valid_util_at?->format('Y-m-d');
    }
}
