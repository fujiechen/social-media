<?php

namespace App\Transformers;

use App\Models\Resource;
use League\Fractal\TransformerAbstract;

class ResourceTransformer extends TransformerAbstract
{
    public function transform(Resource $resource): array
    {
        return [
            'id' => $resource->id,
            'name' => $resource->name,
        ];
    }
}
