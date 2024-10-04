<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $publisher_user_id
 * @property int $following_user_id
 * @property ?Carbon $valid_until_at
 * @property ?int $valid_until_at_days
 * @property ?string $valid_until_at_formatted
 */
class UserFollowing extends Model
{
    use SoftDeletes;
    use HasUser;

    protected $fillable = [
        'publisher_user_id',
        'following_user_id',
        'valid_until_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'valid_until_at' => 'datetime',
    ];

    protected $appends = [
        'valid_until_at_formatted',
    ];

    const USER_SUBSCRIBER_REDIRECT_REGISTRATION = 'registration';
    const USER_SUBSCRIBER_REDIRECT_PRODUCT = 'product';

    public function publisherUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_user_id');
    }

    public function followingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'following_user_id');
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

    public function isExpired(): bool {
        if (is_null($this->valid_until_at_days)) {
            return false;
        }
        return $this->valid_until_at_days <= 0;
    }
}
