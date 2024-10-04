<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesAlbum extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'series_id',
        'album_id',
    ];

}
