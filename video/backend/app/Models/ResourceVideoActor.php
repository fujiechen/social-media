<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceVideoActor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_video_id',
        'resource_actor_id',
    ];

    public function resourceVideo(): BelongsTo
    {
        return $this->belongsTo(ResourceVideo::class);
    }

    public function resourceActor(): BelongsTo
    {
        return $this->belongsTo(ResourceActor::class);
    }
}
