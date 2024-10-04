<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAlbumTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_album_id',
        'resource_tag_id',
    ];

    public function resourceAlbum(): BelongsTo
    {
        return $this->belongsTo(ResourceAlbum::class);
    }

    public function resourceTag(): BelongsTo
    {
        return $this->belongsTo(ResourceTag::class);
    }
}
