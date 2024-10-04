<?php

namespace App\Http\Controllers;

use App\Services\Cache\AppCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppController extends Controller
{
    private AppCacheService $appCacheService;

    public function __construct(AppCacheService $appCacheService) {
        $this->appCacheService = $appCacheService;
    }

    public function categories(): JsonResponse {
        return response()->json($this->appCacheService->getOrCreateAppCategoryList());
    }

    public function index(Request $request): JsonResponse {
        $appCategoryId = $request->input('app_category_id');
        $isHot = $request->input('is_hot');
        return response()->json($this->appCacheService->getOrCreateAppSearchList($appCategoryId, $isHot));
    }
}
