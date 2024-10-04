<?php

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class SyncMediaFavoriteCountEvent
{
    use SerializesModels;

    public Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }
}
