<?php

namespace App\Transformers;

use App\Models\CategoryUser;
use League\Fractal\TransformerAbstract;

class UserCategoryTransformer extends TransformerAbstract
{
    public function transform(?CategoryUser $categoryUser): array
    {
        if (!$categoryUser) {
            return [];
        }

        return [
            'id' => $categoryUser->id,
            'user_id' => $categoryUser->user_id,
            'category_id' => $categoryUser->category->id,
            'category_name' => $categoryUser->category->name,
            'valid_until_at_days' => $categoryUser->valid_until_at_days
        ];
    }
}
