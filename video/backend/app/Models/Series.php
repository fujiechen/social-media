<?php

namespace App\Models;

use App\Events\SeriesDeletedEvent;
use App\Events\SeriesUpdatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property Collection|Video[] $videos
 * @property Collection|Album[] $albums
 * @property Carbon $created_at
 * @property Carbon $updated
 * @property Collection|Tag[] $tags
 * @property File $thumbnailFile
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property Media[]|Collection $medias
 * @property Collection $media_ids
 */
class Series extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'type',
        'description',
        'thumbnail_file_id',
        'preview_file_id',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'video_ids',
        'video_names',
        'album_ids',
        'album_names',
        'media_ids',
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $with = [
        'thumbnailFile',
    ];

    protected $dispatchesEvents = [
        'updated' => SeriesUpdatedEvent::class,
        'deleted' => SeriesDeletedEvent::class,
    ];

    const TYPE_UPLOAD = 'upload';
    const TYPE_CLOUD = 'cloud';
    const TYPE_RESOURCE = 'resource';

    public function videos(): HasManyThrough {
        return $this->hasManyThrough(Video::class, SeriesVideo::class,
            'series_id', 'id', 'id', 'video_id');
    }

    public function albums(): HasManyThrough {
        return $this->hasManyThrough(Album::class, SeriesAlbum::class,
            'series_id', 'id', 'id', 'album_id');
    }

    public function getVideoIdsAttribute(): Collection {
        return $this->videos->pluck('id');
    }

    public function getVideoNamesAttribute(): Collection {
        return $this->videos->pluck('name');
    }

    public function getAlbumIdsAttribute(): Collection {
        return $this->albums->pluck('id');
    }

    public function getAlbumNamesAttribute(): Collection {
        return $this->albums->pluck('name');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function getTagIdsAttribute(): Collection {
        return $this->tags->pluck('id');
    }

    public function getTagNamesAttribute(): Collection {
        return $this->tags->pluck('name');
    }

    public function totalChildrenVideos(): int {
        return $this->videos->count();
    }

    public function totalChildrenAlbums(): int {
        return $this->albums->count();
    }

    public function medias(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    public function getMediaIdsAttribute(): Collection {
        return $this->medias->pluck('id');
    }
}
