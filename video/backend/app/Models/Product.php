<?php

namespace App\Models;

use App\Events\ProductDeletedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $type
 * @property ?int $user_id
 * @property ?int $publisher_user_id
 * @property ?int $role_id
 * @property ?int $media_id
 * @property int $thumbnail_file_id
 * @property int $unit_cents
 * @property string $frequency
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Collection $images
 * @property Collection $medias
 * @property File $thumbnailFile
 * @property ?User $user
 * @property ?Role $role
 * @property ?User $subscriberUser
 * @property ?User $publishUser
 * @property ?Media $media
 * @property float $unit_price
 * @property string $currency_name
 * @property ?int $frequency_as_extend_days
 * @property string $product_user_type
 * @property ?int $order_num_allowance
 * @property bool $is_active
 */
class Product extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;
    use HasUser;
    use SoftDeletes;

    protected $fillable = [
        'order_num_allowance',
        'name',
        'description',
        'user_id',
        'thumbnail_file_id',
        'currency_name',
        'unit_cents',
        'frequency',
        'publisher_user_id',
        'role_id',
        'media_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'is_active',
    ];

    protected $with = [
        'user',
        'thumbnailFile'
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'owner',
        'unit_price',
        'thumbnail_file_path',
        'product_image_ids',
        'product_image_paths',
        'type',
        'file_type',
        'frequency_as_extend_days'
    ];

    protected $dispatchesEvents = [
        'deleted' => ProductDeletedEvent::class,
    ];

    const TYPE_SUBSCRIPTION = 'subscription';
    const TYPE_MEDIA = 'media';
    const TYPE_MEMBERSHIP = 'membership';
    const TYPE_GENERAL = 'general';

    const ONETIME = 'onetime';
    const MONTHLY = 'monthly';
    const QUARTERLY = 'quarterly';
    const YEARLY = 'yearly';

    const PRODUCT_USER_TYPE_USER = 'user';
    const PRODUCT_USER_TYPE_SELF = 'self';

    public function getProductUserTypeAttribute(): string {
        if (empty($this->user_id)) {
            return  self::PRODUCT_USER_TYPE_SELF;
        } else {
            return self::PRODUCT_USER_TYPE_USER;
        }
    }

    public function getTypeAttribute(): string {
        if ($this->publisher_user_id) {
            return self::TYPE_SUBSCRIPTION;
        } else if ($this->role_id) {
            return self::TYPE_MEMBERSHIP;
        } else if ($this->media_id) {
            return self::TYPE_MEDIA;
        } else {
            return self::TYPE_GENERAL;
        }
    }

    public function getOwnerAttribute(): string {
        return $this->user_id == null ? admin_trans_field('platform') : $this->user->nickname;
    }

    public function images(): HasManyThrough
    {
        return $this->hasManyThrough(File::class, ProductImage::class,
            'product_id', 'id', 'id', 'file_id');
    }

    public function getProductImageIdsAttribute(): Collection {
        return $this->images->pluck('id');
    }

    public function getProductImagePathsAttribute(): Collection {
        return $this->images->pluck('upload_path');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function getThumbnailFilePathAttribute(): ?string {
        return $this->thumbnailFile?->upload_path;
    }

    public function publisherUser(): BelongsTo {
        return $this->belongsTo(User::class, 'publisher_user_id');
    }

    public function media(): BelongsTo {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getUnitPriceAttribute(): float {
        return $this->unit_cents / 100;
    }

    public function getFileTypeAttribute(): string {
        return $this->id == null ? 'upload' : 'cloud';
    }

    public function getFrequencyAsExtendDaysAttribute(): ?int {
        if ($this->frequency == self::MONTHLY) {
            return 30;
        }

        if ($this->frequency == self::QUARTERLY) {
            return 90;
        }

        if ($this->frequency == self::YEARLY) {
            return 365;
        }

        return null;
    }

}

