<?php

namespace App\Transformers;

use App\Models\Tutorial;
use League\Fractal\TransformerAbstract;

class TutorialTransformer extends TransformerAbstract
{
    private TutorialFileTransformer $tutorialFileTransformer;

    public function __construct(TutorialFileTransformer $tutorialFileTransformer) {
        $this->tutorialFileTransformer = $tutorialFileTransformer;
    }

    public function transform(?Tutorial $tutorial): array
    {
        if (!$tutorial) {
            return [];
        }

        $tutorialFiles = [];
        foreach ($tutorial->tutorialFiles as $tutorialFile) {
            $tutorialFiles[] = $this->tutorialFileTransformer->transform($tutorialFile);
        }

        return [
            'id' => $tutorial->id,
            'name' => $tutorial->name,
            'content' => $tutorial->content,
            'os' => $tutorial->os,
            'tutorial_files' => $tutorialFiles,
        ];
    }
}
