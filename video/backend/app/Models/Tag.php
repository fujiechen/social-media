<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property ?int $resource_tag_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ResourceTag[]|Collection $resourceTags
 * @property Collection $resource_tag_names
 * @property Collection $resource_tag_ids
 * @property int $views_count
 * @property int $priority
 * @property int $active_media_videos_count
 * @property int $active_media_series_count
 * @property int $active_media_albums_count
 */
class Tag extends Model
{
    protected $fillable = [
        'name',
        'priority',
        'created_at',
        'updated_at',
        'views_count',
        'active_media_videos_count',
        'active_media_series_count',
        'active_media_albums_count',
    ];

    protected $appends = [
        'resource_tag_names',
        'resource_tag_ids',
    ];

    public function resourceTags(): HasMany
    {
        return $this->hasMany(ResourceTag::class, 'tag_id');
    }

    public function getResourceTagNamesAttribute(): Collection {
        return $this->resourceTags->pluck('name');
    }

    public function getResourceTagIdsAttribute(): Collection {
        return $this->resourceTags->pluck('id');
    }
}

