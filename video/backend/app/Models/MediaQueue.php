<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasMedia;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $media_type
 * @property string $name
 * @property string $description
 * @property string $role_ids
 * @property string $status
 * @property string $errors
 * @property BelongsTo $user
 * @property File $thumbnailFile
 * @property HasMany $videoQueues
 * @property HasMany $albumQueues
 * @property ?int $media_id
 * @property int $thumbnail_file_id
 * @property Media $media
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MediaQueue extends Model
{
    use HasUser;
    use HasMedia;
    use HasCreatedAt;

    public $table = 'media_queues';

    protected $fillable = [
        'user_id',
        'media_type',
        'name',
        'description',
        'thumbnail_file_id',
        'role_ids',
        'status',
        'errors',
        'media_id',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'videoQueues',
        'playlistQueues',
        'albumQueues'
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $appends = [
        'created_at_formatted',
        'role_names',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

    const TYPE_SERIES = 'Series';
    const TYPE_VIDEO = 'Video';
    const TYPE_ALBUM = 'Album';
    const TYPE_PLAYLIST = 'Playlist';
    const TYPE_PLAYLIST_BATCH = 'Playlist_Batch';

    public function albumQueues(): HasMany
    {
        return $this->hasMany(AlbumQueue::class, 'media_queue_id');
    }

    public function playlistQueues(): HasMany
    {
        return $this->hasMany(PlaylistQueue::class, 'media_queue_id');
    }

    public function videoQueues(): HasMany
    {
        return $this->hasMany(VideoQueue::class, 'media_queue_id');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function getRoleNamesAttribute(): array {
        $roleNames = [];
        if (empty($this->role_ids)) {
            return [];
        }

        foreach (explode(',' , $this->role_ids) as $roleId) {
            $roleNames[] = Role::roleIdToName($roleId);
        }

        return $roleNames;
    }
}
