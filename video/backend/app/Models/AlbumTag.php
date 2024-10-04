<?php

namespace App\Models;

use App\Events\AlbumTagSavedEvent;
use App\Models\Traits\HasAlbum;
use App\Models\Traits\HasTag;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $album_id
 * @property int $tag_id
 * @property Album $album
 * @property Tag $tag
 */
class AlbumTag extends Model
{
    use HasAlbum;
    use HasTag;

    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'tag_id',
    ];

    protected $dispatchesEvents = [
        'saved' => AlbumTagSavedEvent::class,
    ];
}
