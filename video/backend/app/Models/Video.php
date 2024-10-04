<?php

namespace App\Models;

use App\Events\VideoDeletedEvent;
use App\Events\VideoUpdatedEvent;
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
 * @property int $thumbnail_file_id
 * @property int $video_file_id
 * @property ?int $preview_file_id
 * @property ?int $download_file_id
 * @property Series[]|Collection $series
 * @property Tag[]|Collection $tags
 * @property Category[]|Collection $categories
 * @property Actor[]|Collection $actors
 * @property ResourceVideo $resourceVideo
 * @property File $videoFile
 * @property ?File $previewFile
 * @property File $thumbnailFile
 * @property ?File $downloadFile
 * @property ?int $duration_in_seconds
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?array $meta_json
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property Media[]|Collection $medias
 * @property Collection $media_ids
 */
class Video extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'type',
        'name',
        'description',
        'thumbnail_file_id',
        'video_file_id',
        'resource_video_id',
        'preview_file_id',
        'duration_in_seconds',
        'download_file_id',
        'meta_json',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    protected $appends = [
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
        'videoFile',
        'resourceVideo',
        'previewFile',
        'downloadFile',
    ];

    protected $dispatchesEvents = [
        'updated' => VideoUpdatedEvent::class,
        'deleted' => VideoDeletedEvent::class,
    ];

    const TYPE_UPLOAD = 'upload';
    const TYPE_CLOUD = 'cloud';
    const TYPE_RESOURCE = 'resource';

    public function downloadFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'download_file_id');
    }

    public function resourceVideo(): BelongsTo
    {
        return $this->belongsTo(ResourceVideo::class, 'resource_video_id');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function previewFile(): BelongsTo {
        return $this->belongsTo(File::class, 'preview_file_id');
    }

    public function videoFile(): BelongsTo {
        return $this->belongsTo(File::class, 'video_file_id');
    }

    public function videoTags(): HasMany {
        return $this->hasMany(VideoTag::class);
    }

    public function videoCategories(): HasMany {
        return $this->hasMany(VideoCategory::class);
    }

    public function videoActors(): HasMany {
        return $this->hasMany(VideoActor::class);
    }

    public function series(): HasManyThrough {
        return $this->hasManyThrough(Series::class, SeriesVideo::class,
            'video_id', 'id', 'id', 'series_id');
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, VideoTag::class,
            'video_id', 'id', 'id', 'tag_id');
    }

    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(Category::class, VideoCategory::class,
            'video_id', 'id', 'id', 'category_id');
    }

    public function actors(): HasManyThrough
    {
        return $this->hasManyThrough(Actor::class, VideoActor::class,
            'video_id', 'id', 'id', 'actor_id');
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
