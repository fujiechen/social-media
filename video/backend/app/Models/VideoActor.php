<?php

namespace App\Models;

use App\Events\VideoActorSavedEvent;
use App\Models\Traits\HasActor;
use App\Models\Traits\HasVideo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $video_id
 * @property int $actor_id
 * @property Actor $actor
 * @property Video $video
 */
class VideoActor extends Model
{
    use HasVideo;
    use HasActor;

    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'actor_id',
    ];

    protected $dispatchesEvents = [
        'saved' => VideoActorSavedEvent::class,
    ];
}
