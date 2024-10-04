<?php

namespace App\Events\Tag;

use App\Models\Tag;
use Illuminate\Queue\SerializesModels;

class AddTagViewCountEvent
{
    use SerializesModels;

    public Tag $tag;

    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }
}
