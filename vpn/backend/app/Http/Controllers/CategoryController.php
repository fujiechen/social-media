<?php

namespace App\Http\Controllers;

use App\Services\Cache\CategoryCacheService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    private CategoryCacheService $categoryCacheService;

    public function __construct(CategoryCacheService $categoryCacheService) {
        $this->categoryCacheService = $categoryCacheService;
    }

    public function index(): JsonResponse {
        return response()->json($this->categoryCacheService->getOrCreateCategoryList());
    }

    public function show(int $categoryId): JsonResponse {
        return response()->json($this->categoryCacheService->getOrCategoryShow($categoryId));
    }
}
