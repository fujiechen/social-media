<?php

namespace App\Models;

use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $resource_id
 * @property string $resource_playlist_url
 * @property int $media_queue_id
 * @property array $response
 * @property Resource $resource
 * @property string $status
 * @property string $error
 * @property BelongsTo $playlist
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property BelongsTo $mediaQueue
 */
class PlaylistQueue extends Model
{
    use HasResource;

    protected $fillable = [
        'resource_id',
        'resource_playlist_url',
        'media_queue_id',
        'status',
        'errors',
        'response',
    ];

    protected $casts = [
        'response' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    public function mediaQueue(): BelongsTo {
        return $this->belongsTo(MediaQueue::class, 'media_queue_id');
    }
}
