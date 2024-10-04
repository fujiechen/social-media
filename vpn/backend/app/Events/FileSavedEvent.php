<?php

namespace App\Events;

use App\Models\File;
use Illuminate\Queue\SerializesModels;

class FileSavedEvent
{
    use SerializesModels;

    public File $file;

    public function __construct(File $file) {
        $this->file = $file;
    }
}
