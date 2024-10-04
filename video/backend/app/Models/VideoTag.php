<?php

namespace App\Models;

use App\Events\VideoTagSavedEvent;
use App\Models\Traits\HasTag;
use App\Models\Traits\HasVideo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $video_id
 * @property int $tag_id
 * @property Video $video
 * @property Tag $tag
 */
class VideoTag extends Model
{
    use HasVideo;
    use HasTag;

    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'tag_id',
    ];

    protected $dispatchesEvents = [
        'saved' => VideoTagSavedEvent::class,
    ];
}
