<?php

namespace App\Services\Cache;

use App\Models\Tag;
use App\Transformers\TagTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class TagCacheService
{
    private string $prefix = 'tag_';
    private Fractal $fractal;
    private TagTransformer $tagTransformer;

    public function __construct(Fractal $fractal, TagTransformer $tagTransformer) {
        $this->fractal = $fractal;
        $this->tagTransformer = $tagTransformer;
    }

    public function getOrCreateTagSearchList(?string $name, int $perPage, int $page): array {
        $key = $this->prefix . 'search_name_' . $name . "_per_page_{$perPage}_page_{$page}";
        return Cache::remember($key, 3600, function() use ($name, $perPage, $page){
            $tags = Tag::query();
            if (!empty($name)) {
                $tags->where('name', 'like', '%' . $name . '%');
            }
            $tags = $tags->orderBy('priority', 'desc');
            $tags = $tags->orderBy('views_count', 'desc');
            $tags = $tags->paginate($perPage);
            $resource = new Collection($tags->getCollection(), $this->tagTransformer);
            $resource->setPaginator(new IlluminatePaginatorAdapter($tags));
            return $this->fractal->createData($resource)->toArray();
        });
    }

}
