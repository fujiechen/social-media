<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property ?int $series_id
 * @property ?Series $series
 * @property string $status
 * @property string $errors
 * @property File $thumbnailFile
 * @property HasMany $videoQueues
 * @property HasMany $albumQueues
 * @property ?int $media_queue_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $created_at_formatted
 */
class SeriesQueue extends Model
{
    use HasCreatedAt;
    use HasUser;

    public $table = 'series_queues';

    protected $fillable = [
        'name',
        'description',
        'thumbnail_file_id',
        'resource_series_id',
        'series_id',
        'status',
        'errors',
        'media_queue_id',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'videoQueues',
        'albumQueues',
        'series',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $appends = [
        'created_at_formatted',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    public function albumQueues(): HasMany
    {
        return $this->hasMany(AlbumQueue::class, 'series_queue_id');
    }

    public function videoQueues(): HasMany
    {
        return $this->hasMany(VideoQueue::class, 'series_queue_id');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function series(): BelongsTo {
        return $this->belongsTo(Series::class, 'series_id');
    }
}
