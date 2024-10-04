<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property Media $media
 * @property string $permission
 */
class MediaPermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'permission',
    ];
}
