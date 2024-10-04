<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User $user
 * @property Media $media
 * @property string $type
 */
class MediaLike extends Model
{
    use HasUser;
    use HasMedia;

    protected $table = 'media_likes';

    const TYPE_LIKE = 'like';
    const TYPE_DISLIKE = 'dislike';

    protected $fillable = [
        'user_id',
        'media_id',
        'type',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'media',
    ];

}
