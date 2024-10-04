<?php

namespace App\Transformers;

use App\Models\AppCategory;
use League\Fractal\TransformerAbstract;

class AppCategoryTransformer extends TransformerAbstract
{
    public function transform(AppCategory $appCategory): array
    {
        return [
            'id' => $appCategory->id,
            'name' => $appCategory->name,
        ];
    }
}
