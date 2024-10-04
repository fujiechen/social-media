<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

/**
 * @property $media_id
 * @property $role_id
 */
class MediaRecommendation extends Model
{
    use HasMedia;

    protected $fillable = [
        'media_id',
        'role_id',
    ];
}
