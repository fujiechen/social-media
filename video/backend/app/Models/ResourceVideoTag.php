<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceVideoTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'resource_video_id',
        'resource_tag_id',
    ];



    public function resourceVideo(): BelongsTo
    {
        return $this->belongsTo(ResourceVideo::class);
    }

    public function resourceTag(): BelongsTo
    {
        return $this->belongsTo(ResourceTag::class);
    }
}
