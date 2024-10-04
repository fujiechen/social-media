<?php

namespace App\Models;

use App\Events\AlbumQueueUpdatedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resource_id
 * @property string $resource_album_url
 * @property int $resource_album_id
 * @property int $album_id
 * @property array $response
 * @property Resource $resource
 * @property string $status
 * @property string $error
 * @property BelongsTo $album
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?MediaQueue $mediaQueue
 * @property ?PlaylistQueue $playlistQueue
 * @property ?SeriesQueue $seriesQueue
 * @property ?int $media_queue_id
 * @property ?int $playlist_queue_id
 * @property ?int $series_queue_id
 */
class AlbumQueue extends Model
{
    use HasResource;
    use HasCreatedAt;

    protected $fillable = [
        'resource_id',
        'resource_album_url',
        'media_queue_id',
        'resource_album_id',
        'album_id',
        'status',
        'errors',
        'response',
        'playlist_queue_id',
        'series_queue_id',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING
    ];

    protected $with = [
        'resource',
        'album',
    ];

    protected $appends = [
        'created_at_formatted',
    ];

    protected $dispatchesEvents = [
        'updated' => AlbumQueueUpdatedEvent::class,
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

    public function album():BelongsTo {
        return $this->belongsTo(Album::class, 'album_id');
    }

}
