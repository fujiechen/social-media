<?php

namespace App\Services\Cache;

use App\Models\Actor;
use App\Transformers\ActorTransformer;
use Illuminate\Support\Facades\Cache;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;

class ActorCacheService
{
    private string $prefix = 'actor_';
    private Fractal $fractal;
    private ActorTransformer $actorTransformer;

    public function __construct(Fractal $fractal, ActorTransformer $actorTransformer) {
        $this->fractal = $fractal;
        $this->actorTransformer = $actorTransformer;
    }

    public function getOrCreateActorSearchList(?string $name, int $perPage, int $page): array {
        $key = $this->prefix . 'search_name_' . $name . "_per_page_{$perPage}_page_{$page}";
        return Cache::remember($key, 3600, function() use ($name, $perPage, $page){
            $actors = Actor::query();
            if (!empty($name)) {
                $actors->where('name', 'like', '%' . $name . '%');
            }
            $actors = $actors->orderBy('priority', 'desc');
            $actors = $actors->orderBy('views_count', 'desc');
            $actors = $actors->paginate($perPage);
            $resource = new Collection($actors->getCollection(), $this->actorTransformer);
            $resource->setPaginator(new IlluminatePaginatorAdapter($actors));
            return $this->fractal->createData($resource)->toArray();
        });
    }

}
