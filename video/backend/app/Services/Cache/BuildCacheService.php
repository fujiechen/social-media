<?php

namespace App\Services\Cache;

use App\Models\Media;

class BuildCacheService
{
    private MediaCacheService $mediaCacheService;

    public function __construct(MediaCacheService $mediaCacheService) {
        $this->mediaCacheService = $mediaCacheService;
    }

    public function buildAllCache(): void {
        $this->buildAllVisitorMediaShowCache();
    }

    private function buildAllVisitorMediaShowCache(): void {
        Media::query()->chunk(100, function(Media $media) {
            return $this->mediaCacheService->getOrCreateVisitorMediaShow($media->id);
        });
    }
}
