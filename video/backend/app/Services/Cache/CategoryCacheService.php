<?php

namespace App\Services\Cache;

use App\Models\Category;
use App\Transformers\CategoryTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class CategoryCacheService
{
    private string $prefix = 'category_';
    private Fractal $fractal;
    private CategoryTransformer $categoryTransformer;

    public function __construct(Fractal $fractal, CategoryTransformer $categoryTransformer) {
        $this->fractal = $fractal;
        $this->categoryTransformer = $categoryTransformer;
    }

    public function getOrCreateCategorySearchList(?string $name, int $perPage, int $page): array {
        $key = $this->prefix . 'search_name_' . $name . "_per_page_{$perPage}_page_{$page}";
        return Cache::remember($key, 3600, function() use ($name, $perPage, $page){
            $categories = Category::query();
            if (!empty($name)) {
                $categories->where('name', 'like', '%' . $name . '%');
            }
            $categories = $categories->orderBy('priority', 'desc');
            $categories = $categories->orderBy('views_count', 'desc');
            $categories = $categories->paginate($perPage);
            $resource = new Collection($categories->getCollection(), $this->categoryTransformer);
            $resource->setPaginator(new IlluminatePaginatorAdapter($categories));
            return $this->fractal->createData($resource)->toArray();
        });
    }

}
