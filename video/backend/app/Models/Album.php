<?php

namespace App\Models;

use App\Events\AlbumDeletedEvent;
use App\Events\AlbumUpdatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property File[]|Collection $images
 * @property Collection $image_file_ids
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Category[]|Collection $categories
 * @property Tag[]|Collection $tags
 * @property Actor[]|Collection $actors
 * @property AlbumCategory[]|Collection $albumCategories
 * @property AlbumTag[]|Collection $albumTags
 * @property AlbumActor[]|Collection $albumActors
 * @property ?File $thumbnailFile
 * @property ?File $downloadFile
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property Media[]|Collection $medias
 * @property Collection $media_ids
 * @property ResourceAlbum $resourceAlbum
 */
class Album extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'description',
        'thumbnail_file_id',
        'download_file_id',
        'resource_album_id',
        'meta_json',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    protected $appends = [
        'image_file_ids',
        'image_file_paths',
        'tag_ids',
        'actor_ids',
        'category_ids',
        'tag_names',
        'actor_names',
        'category_names',
        'media_ids',
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $with = [
        'thumbnailFile',
        'images',
        'downloadFile',
        'resourceAlbum',
    ];

    protected $dispatchesEvents = [
        'updated' => AlbumUpdatedEvent::class,
        'deleted' => AlbumDeletedEvent::class,
    ];

    const TYPE_UPLOAD = 'upload';
    const TYPE_CLOUD = 'cloud';
    const TYPE_RESOURCE = 'resource';

    public function thumbnailFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'download_file_id');
    }

    public function resourceAlbum(): BelongsTo
    {
        return $this->belongsTo(ResourceAlbum::class, 'resource_album_id');
    }

    public function images(): HasManyThrough {
        return $this->hasManyThrough(File::class, AlbumFile::class,
            'album_id', 'id', 'id', 'file_id');
    }

    public function getImageFileIdsAttribute(): Collection {
        return $this->images->pluck('id');
    }

    public function getImageFilePathsAttribute(): Collection {
        return $this->images->pluck('upload_path');
    }

    public function albumTags(): HasMany {
        return $this->hasMany(AlbumTag::class);
    }

    public function albumCategories(): HasMany {
        return $this->hasMany(AlbumCategory::class);
    }

    public function albumActors(): HasMany {
        return $this->hasMany(AlbumActor::class);
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, AlbumTag::class,
            'album_id', 'id', 'id', 'tag_id');
    }

    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(Category::class, AlbumCategory::class,
            'album_id', 'id', 'id', 'category_id');
    }

    public function actors(): HasManyThrough
    {
        return $this->hasManyThrough(Actor::class, AlbumActor::class,
            'album_id', 'id', 'id', 'actor_id');
    }

    public function getTagIdsAttribute(): Collection {
        return $this->tags->pluck('id');
    }

    public function getCategoryIdsAttribute(): Collection {
        return $this->categories->pluck('id');
    }

    public function getActorIdsAttribute(): Collection {
        return $this->actors->pluck('id');
    }

    public function getTagNamesAttribute(): Collection {
        return $this->tags->pluck('name');
    }

    public function getCategoryNamesAttribute(): Collection {
        return $this->categories->pluck('name');
    }

    public function getActorNamesAttribute(): Collection {
        return $this->actors->pluck('name');
    }

    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    public function getMediaIdsAttribute(): Collection {
        return $this->medias->pluck('id');
    }
}
