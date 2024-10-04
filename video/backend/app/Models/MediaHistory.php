<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User $user
 * @property Media $media
 */
class MediaHistory extends Model
{
    use HasUser;
    use HasMedia;

    protected $table = 'media_histories';

    protected $fillable = [
        'user_id',
        'media_id',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'media',
    ];

}
