<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeriesVideo extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'series_id',
        'video_id',
    ];

}
