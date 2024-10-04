<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use App\Models\Traits\HasTag;
use Illuminate\Database\Eloquent\Model;

class MediaTag extends Model
{
    use HasMedia;
    use HasTag;

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'tag_id',
    ];

    protected $with = [
        'media',
        'tag',
    ];
}
