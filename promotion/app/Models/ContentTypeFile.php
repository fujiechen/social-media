<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $content_type_id
 * @property int $file_id
 */
class ContentTypeFile extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'content_type_id',
        'file_id',
    ];
}
