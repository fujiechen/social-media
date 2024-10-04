<?php

namespace App\Transformers;

use App\Models\User;
use App\Models\UserFollowing;
use App\Services\UserFollowingService;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private UserRoleTransformer $userRoleTransformer;
    private UserFollowingService $userFollowingService;

    public function __construct(UserRoleTransformer $userRoleTransformer,
                                FileTransformer $fileTransformer,
                                UserFollowingService $userFollowingService) {
        $this->userRoleTransformer = $userRoleTransformer;
        $this->fileTransformer = $fileTransformer;
        $this->userFollowingService = $userFollowingService;
    }

    public function transform(?User $user): array
    {
        if (empty($user)) {
            return [];
        }

        $includes = [];
        if ($this->getCurrentScope()) {
            $includes = $this->getCurrentScope()->getManager()->getRequestedIncludes();
        }


        $data = [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar_file' => $this->fileTransformer->transform($user->avatarFile),
            'views_count' => $user->views_count,
            'priority' => $user->priority,
        ];

        if (in_array('access_token', $includes)) {
            $data['access_token'] = $user->access_token;
        }

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

        if (in_array('subscriptions_count', $includes)) {
            $data['publisher'] = [
                'subscriptions_count' => $user->totalSubscriptions(),
                'subscribers_count' => $user->totalFollowerUsers(),
                'is_followed' => false,
            ];

            $currentUser = null;
            if (auth('api')->check()) {
                $currentUser = auth('api')->user();
                $userFollowing = $this->userFollowingService->getUserFollowing($currentUser->id, $user->id);
                $data['publisher']['is_followed'] = $userFollowing != null;

                if ($userFollowing) {
                    $data['publisher']['valid_until_at_days'] = $userFollowing->valid_until_at_days;
                    $data['publisher']['valid_until_at_formatted'] = $userFollowing->valid_until_at_formatted;
                }

            }

            $redirect = $this->userFollowingService->getUserSubscriptionRedirect($currentUser?->id, $user->id);
            $data['publisher']['follows'] = [
                'registration_redirect' => $redirect == UserFollowing::USER_SUBSCRIBER_REDIRECT_REGISTRATION,
                'product_redirect' => $redirect == UserFollowing::USER_SUBSCRIBER_REDIRECT_PRODUCT,
            ];
        }

        if (in_array('medias_count', $includes)) {
            $seriesCount = $user->totalMediaSeries();
            $videosCount = $user->totalMediaVideos();
            $albumsCount = $user->totalMediaAlbums();

            $data['medias'] = [
                'medias_count' => $seriesCount + $videosCount + $albumsCount,
                'series_count' => $seriesCount,
                'videos_count' => $videosCount,
                'albums_count' => $albumsCount,
            ];
        }

        return $data;
    }


}
