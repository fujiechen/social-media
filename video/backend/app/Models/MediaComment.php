<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @proerty int $user_id
 * @property int $media_id
 * @property User $user
 * @property Media $media
 * @property string $comment
 * @property int $created_at
 * @property int $updated_at
 * @property ?int $deleted_at
 */
class MediaComment extends Model
{
    use HasUser;
    use HasMedia;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'media_id',
        'comment',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
        'media',
    ];


}
