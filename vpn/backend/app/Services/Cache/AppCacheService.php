<?php

namespace App\Services\Cache;

use App\Services\AppService;
use App\Transformers\AppCategoryTransformer;
use App\Transformers\AppTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Collection;

class AppCacheService
{
    private string $prefix = 'app_';

    private Fractal $fractal;
    private AppService $appService;
    private AppTransformer $appTransformer;
    private AppCategoryTransformer $appCategoryTransformer;

    public function __construct(Fractal        $fractal, AppService $appService,
                                AppTransformer $appTransformer, AppCategoryTransformer $appCategoryTransformer)
    {
        $this->fractal = $fractal;
        $this->appService = $appService;
        $this->appTransformer = $appTransformer;
        $this->appCategoryTransformer = $appCategoryTransformer;
    }

    public function getOrCreateAppSearchList(?int $appCategoryId, ?bool $isHot): array
    {
        $key = $this->prefix . 'list_' . $appCategoryId . '_' . $isHot;
        return Cache::remember($key, 3600, function () use ($appCategoryId, $isHot) {
            $apps = $this->appService->fetchAllAppsQuery($appCategoryId, $isHot)->get();
            $resource = new Collection($apps, $this->appTransformer);
            return $this->fractal->createData($resource)->toArray();
        });
    }

    public function getOrCreateAppCategoryList(): array {
        $key = $this->prefix . 'category_list';
        return Cache::remember($key, 3600, function () {
            $apps = $this->appService->fetchAllAppCategories();
            $resource = new Collection($apps, $this->appCategoryTransformer);
            return $this->fractal->createData($resource)->toArray();
        });
    }
}
