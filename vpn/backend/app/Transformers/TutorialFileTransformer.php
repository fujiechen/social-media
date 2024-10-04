<?php

namespace App\Transformers;

use App\Models\TutorialFile;
use League\Fractal\TransformerAbstract;

class TutorialFileTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;

    public function __construct(FileTransformer $fileTransformer) {
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(TutorialFile $tutorialFile): array
    {
        return [
            'id' => $tutorialFile->id,
            'name' => $tutorialFile->name,
            'file' => $this->fileTransformer->transform($tutorialFile->file)
        ];
    }
}
