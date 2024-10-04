<?php

namespace App\Services\Cache;

use App\Models\Category;
use App\Services\CategoryService;
use App\Transformers\CategoryTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class CategoryCacheService
{
    private string $prefix = 'category_';

    private Fractal $fractal;
    private CategoryService $categoryService;
    private CategoryTransformer $categoryTransformer;

    public function __construct(Fractal $fractal, CategoryService $categoryService, CategoryTransformer $categoryTransformer) {
        $this->fractal = $fractal;
        $this->categoryService = $categoryService;
        $this->categoryTransformer = $categoryTransformer;
    }

    public function getOrCreateCategoryList(): array {
        $key = $this->prefix . 'list';
        return Cache::remember($key, 3600, function() {
            $categories = $this->categoryService->fetchAllCategoriesQuery()->get();
            $resource = new Collection($categories, $this->categoryTransformer);
            return $this->fractal->createData($resource)->toArray();
        });
    }

    public function getOrCategoryShow(int $categoryId): array {
        $key = $this->prefix . $categoryId;
        return Cache::remember($key, 3600, function() use ($categoryId) {
            $category = Category::find($categoryId);
            $resource = new Item($category, $this->categoryTransformer);
            return $this->fractal->createData($resource)->toArray();
        });
    }
}
