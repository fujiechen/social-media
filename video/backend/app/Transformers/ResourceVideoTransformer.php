<?php

namespace App\Transformers;

use App\Models\ResourceVideo;
use League\Fractal\TransformerAbstract;

class ResourceVideoTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer) {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(ResourceVideo $resourceVideo): array
    {
        return [
            'id' => $resourceVideo->id,
            'name' => $resourceVideo->name,
            'description' => $resourceVideo->description,
            'created_at' => $resourceVideo->created_at,
            'resource_file' => $this->fileTransformer->transform($resourceVideo->file)
        ];
    }


}
