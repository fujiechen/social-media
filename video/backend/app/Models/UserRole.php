<?php

namespace App\Models;

use App\Events\UserRoleSavedEvent;
use App\Models\Traits\HasUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $role_id
 * @property ?Carbon $valid_until_at
 * @property ?string $valid_until_at_formatted
 * @property ?int $valid_until_at_days
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
        'valid_until_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
        'created_at' => 'datetime',
        'valid_until_at' => 'datetime',
    ];

    protected $appends = [
        'valid_until_at_formatted',
    ];

    protected $dispatchesEvents = [
        'saved' => UserRoleSavedEvent::class,
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

    public function getValidUntilAtFormattedAttribute(): ?string {
        return $this->valid_until_at?->format('Y-m-d');
    }

    public function getValidUntilAtDaysAttribute(): ?int {
        if ($this->valid_until_at) {
            $now = Carbon::now();
            $days = $now->diffInDays($this->valid_until_at) + 1;
            if ($this->valid_until_at->isAfter($now)) {
                return $days;
            }
            return - $days;
        }
        return null;
    }
}
