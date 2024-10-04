<?php

namespace App\Transformers;

use App\Models\UserSearch;
use League\Fractal\TransformerAbstract;

class UserSearchTransformer extends TransformerAbstract
{
    public function transform(UserSearch $userSearch): array
    {
        return [
            'search' => $userSearch->search,
            'count' => $userSearch->search_count,
        ];
    }
}
