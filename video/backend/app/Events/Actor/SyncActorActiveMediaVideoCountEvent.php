<?php

namespace App\Events\Actor;

use App\Models\Media;
use Illuminate\Queue\SerializesModels;

class SyncActorActiveMediaVideoCountEvent
{
    use SerializesModels;

    use SerializesModels;

    public Media $media;
    public bool $isActive;

    public function __construct(Media $media, bool $isActive)
    {
        $this->media = $media;
        $this->isActive = $isActive;
    }
}
