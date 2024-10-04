<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlbumFile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'file_id',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }


    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

}
