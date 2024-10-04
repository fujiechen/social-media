<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


/**
 * @property int $id
 * @property Resource $resource
 * @property int $resource_id
 * @property string $name
 * @property string $description
 * @property string $resource_album_url
 * @property ?int $download_file_id
 * @property ?int $thumbnail_file_id
 * @property ?File $downloadFile
 * @property ?File $thumbnailFile
 * @property ResourceTag[]|Collection $resourceTags
 * @property ResourceActor[]|Collection $resourceActors
 * @property ResourceCategory[]|Collection $resourceCategories
 * @property ResourceAlbumFile[]|Collection $resourceAlbumFiles
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?array $meta_json
 */
class ResourceAlbum extends Model
{
    use HasResource;
    use HasCreatedAt;

    protected $fillable = [
        'resource_id',
        'resource_album_url',
        'name',
        'description',
        'thumbnail_file_id',
        'download_file_id',
        'meta_json',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    protected $appends = [
        'resource_tag_names',
        'resource_actor_names',
        'resource_category_names',
        'image_file_ids',
        'image_file_paths',
        'image_file_names_string',
        'created_at_formatted',
        'resource_tag_names_string',
        'resource_actor_names_string',
        'resource_category_names_string',
    ];

    protected $with = [
        'thumbnailFile',
        'downloadFile',
        'resourceAlbumFiles'
    ];

    const TYPE_UPLOAD = 'upload';
    const TYPE_CLOUD = 'cloud';

    public function getImageFileIdsAttribute(): Collection {
        return $this->resourceAlbumFiles->pluck('file_id');
    }

    public function getImageFilePathsAttribute(): Collection {
        return $this->resourceAlbumFiles->pluck('upload_path');
    }

    public function thumbnailFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'download_file_id');
    }

    public function resourceTags(): HasManyThrough {
        return $this->hasManyThrough(ResourceTag::class, ResourceAlbumTag::class,
            'resource_album_id', 'id', 'id', 'resource_tag_id');
    }

    public function resourceCategories(): HasManyThrough {
        return $this->hasManyThrough(ResourceCategory::class, ResourceAlbumCategory::class,
            'resource_album_id', 'id', 'id', 'resource_category_id');
    }

    public function resourceActors(): HasManyThrough {
        return $this->hasManyThrough(ResourceActor::class, ResourceAlbumActor::class,
            'resource_album_id', 'id', 'id', 'resource_actor_id');
    }

    public function getResourceTagNamesAttribute(): Collection {
        return $this->resourceTags->pluck('name');
    }

    public function getResourceActorNamesAttribute(): Collection {
        return $this->resourceActors->pluck('name');
    }

    public function getResourceCategoryNamesAttribute(): Collection {
        return $this->resourceCategories->pluck('name');
    }

    public function resourceAlbumFiles(): HasManyThrough {
        return $this->hasManyThrough(File::class, ResourceAlbumFile::class,
            'resource_album_id', 'id', 'id', 'file_id');
    }

    public function getImageFileNamesStringAttribute(): string {
        return implode(' , ', $this->resourceAlbumFiles->pluck('name')->toArray());
    }

    public function getResourceTagNamesStringAttribute(): string {
        return implode(' , ', $this->resource_tag_names->toArray());
    }

    public function getResourceActorNamesStringAttribute(): string {
        return implode(' , ', $this->resource_actor_names->toArray());
    }

    public function getResourceCategoryNamesStringAttribute(): string {
        return implode(' , ', $this->resource_category_names->toArray());
    }
}
