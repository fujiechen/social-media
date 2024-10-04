<?php

namespace App\Models;

use App\Events\AlbumActorSavedEvent;
use App\Models\Traits\HasActor;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAlbum;


/**
 * @property int $album_id
 * @property int $actor_id
 * @property Album $album
 * @property Actor $actor
 */
class AlbumActor extends Model
{
    use HasAlbum;
    use HasActor;

    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'actor_id',
    ];

    protected $dispatchesEvents = [
        'saved' => AlbumActorSavedEvent::class,
    ];
}
