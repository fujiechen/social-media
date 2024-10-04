<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceAlbumCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_album_id',
        'resource_category_id',
    ];

    public function resourceAlbum(): BelongsTo
    {
        return $this->belongsTo(ResourceAlbum::class);
    }

    public function resourceCategory(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class);
    }
}
