<?php

namespace App\Services;

use App\Dtos\ContentTypeDto;
use App\Models\ContentType;
use App\Models\ContentTypeFile;
use Illuminate\Support\Facades\DB;

class ContentTypeService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function updateOrCreateContentType(ContentTypeDto $dto): ContentType
    {
        return DB::transaction(function () use ($dto) {
            $contentType = ContentType::updateOrCreate([
                'id' => $dto->id
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
            ]);

            ContentTypeFile::query()->where('content_type_id', '=', $contentType->id)->delete();

            foreach ($dto->fileDtos as $fileDto) {
                $fileId = $this->fileService->getOrCreateFile($fileDto)->id;
                ContentTypeFile::create([
                    'content_type_id' => $contentType->id,
                    'file_id' => $fileId
                ]);
            }

            return $contentType;
        });
    }
}
