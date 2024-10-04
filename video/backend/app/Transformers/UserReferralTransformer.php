<?php

namespace App\Transformers;

use App\Models\Tag;
use App\Models\UserReferral;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class UserReferralTransformer extends TransformerAbstract
{
    private UserRoleTransformer $userRoleTransformer;

    public function __construct(
        UserRoleTransformer $userRoleTransformer
    ) {
        $this->userRoleTransformer = $userRoleTransformer;
    }

    public function transform(?UserReferral $userReferral): array
    {
        if (empty($userReferral)) {
            return [];
        }

        $data = [
            'id' => $userReferral->id,
            'user_id' => $userReferral->user_id,
            'sub_user_id' => $userReferral->sub_user_id,
            'level' => $userReferral->level,
            'sub_user_nickname' => Str::mask($userReferral->subUser->nickname, '*', 1),
            'created_at_formatted' => $userReferral->created_at_formatted
        ];

        $user = $userReferral->user;
        foreach ($user->userRoles as $userRole) {
            $userRoleTransformer = $this->userRoleTransformer->transform($userRole);
            $data['top_user_role'] = $userRoleTransformer;
        }

        return $data;
    }
}
