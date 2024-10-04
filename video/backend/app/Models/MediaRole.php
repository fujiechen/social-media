<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaRole extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'role_id',
    ];
}
