<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property int $avatar_file_id
 * @property ResourceCategory[]|Collection $resourceCategories
 * @property Collection  $resource_category_ids
 * @property Collection $resource_category_names
 * @property ?File $avatarFile
 * @property ?string $type
 * @property HasManyThrough $medias
 * @property int $views_count
 * @property int $priority
 * @property int $active_media_videos_count
 * @property int $active_media_series_count
 * @property int $active_media_albums_count
 */
class Category extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'avatar_file_id',
        'created_at',
        'updated_at',
        'views_count',
        'active_media_videos_count',
        'active_media_series_count',
        'active_media_albums_count',
    ];

    protected $appends = [
        'resource_category_names',
        'resource_category_ids',
        'type',
    ];

    const TYPE_CLOUD = 'cloud';
    const TYPE_UPLOAD = 'upload';

    public function avatarFile(): BelongsTo {
        return $this->belongsTo(File::class, 'avatar_file_id');
    }

    public function resourceCategories(): HasMany
    {
        return $this->hasMany(ResourceCategory::class, 'category_id');
    }

    public function getResourceCategoryNamesAttribute(): Collection {
        return $this->resourceCategories->pluck('name');
    }

    public function getResourceCategoryIdsAttribute(): Collection {
        return $this->resourceCategories->pluck('id');
    }

    public function getTypeAttribute(): ?string {
        return $this->id ? self::TYPE_CLOUD : self::TYPE_UPLOAD;
    }

    public function medias(): HasManyThrough {
        return $this->hasManyThrough(Media::class, MediaCategory::class,
            'category_id', 'id', 'id', 'media_id');
    }
}
