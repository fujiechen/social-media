<?php

namespace App\Transformers;

use App\Models\UserRole;
use League\Fractal\TransformerAbstract;

class UserRoleTransformer extends TransformerAbstract
{
    private RoleTransformer $roleTransformer;

    public function __construct(RoleTransformer $roleTransformer) {
        $this->roleTransformer = $roleTransformer;
    }

    public function transform(UserRole $userRole): array
    {

        return [
            'valid_util_at_formatted' => $userRole->valid_util_at_formatted,
            'role' => $this->roleTransformer->transform($userRole->role),
        ];
    }
}
