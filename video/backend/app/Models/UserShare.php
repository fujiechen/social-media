<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $shareable_type
 * @property Media | User $shareable
 * @property int $shareable_id
 * @property string $url
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property string $created_at_formatted
 */
class UserShare extends Model
{
    use HasUser;
    use HasCreatedAt;

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
    ];

    const TYPE_MEDIA = 'media';
    const TYPE_USER = 'user';
    const TYPE_PRODUCT = 'product';
    const TYPE_GROUP_ORDER = 'group_order';

    public static function toShareableType(string $type):string {
        return match ($type) {
            self::TYPE_MEDIA => Media::class,
            self::TYPE_USER => User::class,
            self::TYPE_PRODUCT => Product::class,
        };
    }

    public static function toType(string $shareableType):string {
        return match ($shareableType) {
            Media::class => self::TYPE_MEDIA,
            User::class => self::TYPE_USER,
            Product::class => self::TYPE_PRODUCT,
        };
    }

    public function shareable(): MorphTo {
        return $this->morphTo();
    }

    public function isMedia(): bool {
        return $this->shareable_type == Media::class;
    }

    public function isUser(): bool {
        return $this->shareable_type == User::class;
    }

    public function isProduct(): bool {
        return $this->shareable_type == Product::class;
    }
}
