<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private UserRoleTransformer $userRoleTransformer;

    public function __construct(UserRoleTransformer $userRoleTransformer, FileTransformer $fileTransformer) {
        $this->userRoleTransformer = $userRoleTransformer;
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(User $user): array
    {
        $includes = [];
        if ($this->getCurrentScope()) {
            $includes = $this->getCurrentScope()->getManager()->getRequestedIncludes();
        }


        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'access_token' => $user->access_token,
            'avatar_file' => $this->fileTransformer->transform($user->avatarFile)
        ];

        if (in_array('email', $includes)) {
            $data['email'] = $user->email;
        }

        if (in_array('roles', $includes)) {
            $userRoles = [];
            foreach ($user->userRoles as $userRole) {
                $userRoleTransformer = $this->userRoleTransformer->transform($userRole);
                $userRoles[] = $userRoleTransformer;
                $data['top_user_role'] = $userRoleTransformer;
            }
            $data['user_roles'] = $userRoles;
        }

        return $data;
    }


}
