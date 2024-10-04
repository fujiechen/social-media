<?php

namespace App\Models;

use App\Events\CategoryUserCreatedEvent;
use App\Events\CategoryUserUpdatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Category $category
 * @property int $category_id
 * @property User $user
 * @property int $user_id
 * @property Carbon $valid_until_at
 * @property ?string valid_until_at_formatted
 * @property ?int $valid_until_at_days
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property bool $vpn_server_synced
 */
class CategoryUser extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;
    use HasUser;

    protected $fillable = [
        'user_id',
        'category_id',
        'valid_until_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'vpn_server_synced',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'valid_until_at_formatted',
        'valid_until_at_days',
    ];

    protected $casts = [
        'valid_until_at' => 'date',
        'vpn_server_synced' => 'boolean'
    ];

    protected $attributes = [
        'vpn_server_synced' => false,
    ];

    protected $with = [
        'user',
        'category',
    ];

    protected $dispatchesEvents = [
        'created' => CategoryUserCreatedEvent::class,
        'updated' => CategoryUserUpdatedEvent::class,
    ];

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function getValidUntilAtFormattedAttribute(): ?string {
        return $this->valid_until_at->format('Y-m-d');
    }

    public function getValidUntilAtDaysAttribute(): ?int {
        if ($this->valid_until_at) {
            $now = Carbon::now();
            $days = $now->diffInDays($this->valid_until_at);
            if ($this->valid_until_at->isAfter($now)) {
                return $days + 1;
            }
            return - $days -1;
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
