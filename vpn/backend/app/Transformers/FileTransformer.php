<?php

namespace App\Transformers;

use App\Models\File;
use App\Services\FileService;
use League\Fractal\TransformerAbstract;

class FileTransformer extends TransformerAbstract
{
    public function transform(?File $file): array
    {
        if (!$file) {
            return [];
        }

        return [
            'id' => $file->id,
            'url' => $file->url,
            'created_at_formatted' => $file->created_at_formatted,
        ];
    }

}
