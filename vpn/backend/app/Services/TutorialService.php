<?php

namespace App\Services;

use App\Dtos\TutorialDto;
use App\Dtos\TutorialFileDto;
use App\Models\Tutorial;
use App\Models\TutorialFile;
use Illuminate\Support\Facades\DB;

class TutorialService
{
    public function fetchTutorialByOs(string $os): ?Tutorial
    {
        return Tutorial::where('os', '=', $os)->first();
    }

    public function updateOrCreateTutorial(TutorialDto $dto): Tutorial {
        return DB::transaction(function() use ($dto) {
            /**
             * @var Tutorial $tutorial
             */
            $tutorial = Tutorial::query()->updateOrCreate([
                'id' => $dto->tutorialId,
            ], [
                'name' => $dto->name,
                'content' => $dto->content,
                'os' => $dto->os,
            ]);

            TutorialFile::query()
                ->where('tutorial_id', '=', $tutorial->id)
                ->delete();

            /**
             * @var TutorialFileDto $tutorialFileDto
             */
            foreach ($dto->tutorialFileDtos as $tutorialFileDto) {
                TutorialFile::create([
                    'name' => $tutorialFileDto->name,
                    'tutorial_id' => $tutorial->id,
                    'file_id' => $tutorialFileDto->fileId,
                ]);
            }

            return $tutorial;
        });
    }
}
