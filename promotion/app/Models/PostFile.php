<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $post_id
 * @property int $file_id
 */
class PostFile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'file_id',
    ];
}
