<?php

namespace App\Models;

use App\Events\VideoQueueUpdatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resource_id
 * @property string $resource_video_url
 * @property int $resource_video_id
 * @property int $video_id
 * @property array $response
 * @property Resource $resource
 * @property string $status
 * @property string $error
 * @property BelongsTo $video
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?MediaQueue $mediaQueue
 * @property ?PlaylistQueue $playlistQueue
 * @property ?SeriesQueue $seriesQueue
 * @property ?int $media_queue_id
 * @property ?int $playlist_queue_id
 * @property ?int $series_queue_id
 * @property ?array $prefill_json
 */
class VideoQueue extends Model
{
    use HasResource;
    use HasCreatedAt;

    protected $fillable = [
        'resource_id',
        'resource_video_url',
        'media_queue_id',
        'resource_video_id',
        'video_id',
        'status',
        'errors',
        'response',
        'playlist_queue_id',
        'prefill_json',
        'series_queue_id',
    ];

    protected $casts = [
        'response' => 'array',
        'prefill_json' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING
    ];

    protected $with = [
        'resource',
        'video',
    ];

    protected $appends = [
        'created_at_formatted',
    ];

    protected $dispatchesEvents = [
        'updated' => VideoQueueUpdatedEvent::class,
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    public function playlistQueue(): BelongsTo {
        return $this->belongsTo(PlaylistQueue::class, 'playlist_queue_id');
    }

    public function mediaQueue(): BelongsTo {
        return $this->belongsTo(MediaQueue::class, 'media_queue_id');
    }

    public function seriesQueue(): BelongsTo {
        return $this->belongsTo(SeriesQueue::class, 'series_queue_id');
    }

    public function video():BelongsTo {
        return $this->belongsTo(Video::class, 'video_id');
    }

}
