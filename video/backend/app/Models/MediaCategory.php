<?php

namespace App\Models;

use App\Models\Traits\HasCategory;
use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class MediaCategory extends Model
{
    use HasMedia;
    use HasCategory;

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'category_id',
    ];
}
