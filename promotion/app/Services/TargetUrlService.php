<?php

namespace App\Services;

use App\Dtos\TargetUrlDto;
use App\Models\TargetUrl;
use Illuminate\Support\Facades\DB;

class TargetUrlService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function updateOrCreate(TargetUrlDto $dto): TargetUrl
    {
        return DB::transaction(function () use ($dto) {
            $qrFileId = null;
            if ($dto->qrFileDto) {
                $qrFileId = $this->fileService->getOrCreateFile($dto->qrFileDto)->id;
            }

            return TargetUrl::updateOrCreate([
                'id' => $dto->id
            ], [
                'name' => $dto->name,
                'url' => $dto->url,
                'status' => $dto->status,
                'qr_file_id' => $qrFileId
            ]);
        });
    }
}
