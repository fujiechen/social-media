<?php

namespace App\Models;

use App\Models\Traits\HasActor;
use App\Models\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class MediaActor extends Model
{
    use HasMedia;
    use HasActor;

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'actor_id',
    ];
}
