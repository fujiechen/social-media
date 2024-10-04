<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;


/**
 * @property int $id
 * @property int $resource_id
 * @property string $name
 * @property string $description
 * @property string $resource_video_url
 * @property int $thumbnail_file_id
 * @property ?int $preview_file_id
 * @property ?int $download_file_id
 * @property int $file_id
 * @property File $file
 * @property File $thumbnailFile
 * @property ?File $previewFile
 * @property ?File $downloadFile
 * @property ResourceTag[]|Collection $resourceTags
 * @property ResourceActor[]|Collection $resourceActors
 * @property ResourceCategory[]|Collection $resourceCategories
 * @property ?int $duration_in_seconds
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Resource $resource
 * @property ?array $meta_json
 */
class ResourceVideo extends Model
{
    use HasResource;
    use HasCreatedAt;

    protected $fillable = [
        'resource_id',
        'name',
        'description',
        'resource_video_url',
        'thumbnail_file_id',
        'preview_file_id',
        'download_file_id',
        'file_id',
        'duration_in_seconds',
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
        'resource_tag_names_string',
        'resource_actor_names_string',
        'resource_category_names_string',
        'created_at_formatted'
    ];

    protected $with = [
        'file',
        'thumbnailFile',
        'previewFile',
        'downloadFile',
    ];

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'download_file_id');
    }

    public function thumbnailFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function previewFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'preview_file_id');
    }

    public function resourceTags(): HasManyThrough {
        return $this->hasManyThrough(ResourceTag::class, ResourceVideoTag::class,
            'resource_video_id', 'id', 'id', 'resource_tag_id');
    }

    public function resourceCategories(): HasManyThrough {
        return $this->hasManyThrough(ResourceCategory::class, ResourceVideoCategory::class,
            'resource_video_id', 'id', 'id', 'resource_category_id');
    }

    public function resourceActors(): HasManyThrough {
        return $this->hasManyThrough(ResourceActor::class, ResourceVideoActor::class,
            'resource_video_id', 'id', 'id', 'resource_actor_id');
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
