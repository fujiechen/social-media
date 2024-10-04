<?php

namespace App\Services;

use App\Dtos\AppDto;
use App\Models\App;
use App\Models\AppCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AppService
{
    private FileService $fileService;

    public function __construct(FileService $fileService) {
        $this->fileService = $fileService;
    }

    public function fetchAllAppCategories(): Collection {
        return AppCategory::query()->get();
    }

    public function fetchAllAppsQuery(?int $appCategoryId, ?bool $isHot): Builder
    {
        $query = App::query();

        if ($appCategoryId) {
            $query->where('app_category_id', '=', $appCategoryId);
        }

        if ($isHot) {
            $query->where('is_hot', '=', $isHot);
        }

        return $query;
    }

    public function updateOrCreateApp(AppDto $dto): App {
        return DB::transaction(function() use ($dto) {
            $iconFileId = $this->fileService->getOrCreateFile($dto->iconFileDto)->id;

            /**
             * @var App $app
             */
            $app = App::query()->updateOrCreate([
                'id' => $dto->appId,
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
                'icon_file_id' => $iconFileId,
                'app_category_id' => $dto->appCategoryId,
                'is_hot' => $dto->isHot,
                'url' => $dto->url,
            ]);

            return $app;
        });
    }
}
