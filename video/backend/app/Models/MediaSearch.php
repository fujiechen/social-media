<?php

namespace App\Models;

use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class MediaSearch extends Model
{
    use HasMedia;

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'search_text',
    ];
}
