<?php

namespace App\Models;

use App\Events\VideoCategorySavedEvent;
use App\Models\Traits\HasCategory;
use App\Models\Traits\HasVideo;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $video_id
 * @property int $category_id
 * @property Tag $tag
 * @property Video $video
 */
class VideoCategory extends Model
{
    use HasVideo;
    use HasCategory;

    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'category_id',
    ];

    protected $dispatchesEvents = [
        'saved' => VideoCategorySavedEvent::class,
    ];
}
