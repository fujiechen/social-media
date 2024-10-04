<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $shareable_type
 * @property Category | Order | User $shareable
 * @property int $shareable_id
 * @property string $url
 * @property Carbon $updated_at
 * @property Carbon $created_at
 */
class UserShare extends Model
{
    use HasUser;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'user_id',
        'shareable_type',
        'shareable_id',
        'url',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    const TYPE_USER = 'user';
    const TYPE_CATEGORY = 'category';
    const TYPE_ORDER = 'order';
    const TYPE_GROUP_ORDER = 'group_order'; //TODO

    public static function toShareableType(string $type):string {
        return match ($type) {
            self::TYPE_USER => User::class,
            self::TYPE_CATEGORY => Category::class,
            self::TYPE_ORDER => Order::class,
        };
    }

    public static function toType(string $shareableType):string {
        return match ($shareableType) {
            User::class => self::TYPE_USER,
            Category::class => self::TYPE_CATEGORY,
            Order::class => self::TYPE_ORDER,
        };
    }

    public function shareable(): MorphTo {
        return $this->morphTo();
    }

    public function isOrder(): bool {
        return $this->shareable_type == Order::class;
    }

    public function isCategory(): bool {
        return $this->shareable_type == Category::class;
    }

    public function isUser(): bool {
        return $this->shareable_type == User::class;
    }

}
