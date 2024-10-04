<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property ?int $resource_actor_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?File $avatarFile
 * @property ResourceActor[]|Collection $resourceActors
 * @property Collection $resource_actor_names
 * @property Collection $resource_actor_ids
 * @property int $avatar_file_id
 * @property HasManyThrough $medias
 * @property int $views_count
 * @property int $priority
 * @property int $active_media_videos_count
 * @property int $active_media_series_count
 * @property int $active_media_albums_count
 */
class Actor extends Model
{
    protected $fillable = [
        'name',
        'description',
        'country',
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
        'resource_actor_names',
        'resource_actor_ids',
        'type',
    ];

    const TYPE_CLOUD = 'cloud';
    const TYPE_UPLOAD = 'upload';

    public function avatarFile(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function resourceActors(): HasMany
    {
        return $this->hasMany(ResourceActor::class, 'actor_id');
    }

    public function getResourceActorNamesAttribute(): Collection {
        return $this->resourceActors->pluck('name');
    }

    public function getResourceActorIdsAttribute(): Collection {
        return $this->resourceActors->pluck('id');
    }

    public function getTypeAttribute(): ?string {
        return $this->id ? self::TYPE_CLOUD : self::TYPE_UPLOAD;
    }

    public function medias(): HasManyThrough {
        return $this->hasManyThrough(Media::class, MediaActor::class,
            'actor_id', 'id', 'id', 'media_id');
    }
}
