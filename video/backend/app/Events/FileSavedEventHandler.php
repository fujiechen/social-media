<?php

namespace App\Events;

use App\Models\File;
use App\Services\FileService;
use Illuminate\Contracts\Queue\ShouldQueue;

class FileSavedEventHandler implements ShouldQueue
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function handle(FileSavedEvent $fileSavedEvent): bool
    {
        if ($fileSavedEvent->file->bucket_type == File::TYPE_LOCAL_BUCKET) {
            return true;
        }

        return $this->fileService->uploadFileAndRemoveLocal($fileSavedEvent->file->id);
    }

}
