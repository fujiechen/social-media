<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAlbumActor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_album_id',
        'resource_actor_id',
    ];

    public function resourceAlbum(): BelongsTo
    {
        return $this->belongsTo(ResourceAlbum::class);
    }

    public function resourceActor(): BelongsTo
    {
        return $this->belongsTo(ResourceActor::class);
    }
}
