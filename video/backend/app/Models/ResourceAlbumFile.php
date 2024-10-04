<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $resource_album_id
 * @property int $file_id
 */
class ResourceAlbumFile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_album_id',
        'file_id',
    ];

    public function resourceAlbum(): BelongsTo
    {
        return $this->belongsTo(ResourceAlbum::class);
    }


    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

}
