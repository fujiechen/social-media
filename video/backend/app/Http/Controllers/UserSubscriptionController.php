<?php

namespace App\Http\Controllers;

use App\Exceptions\IllegalArgumentException;
use App\Models\User;
use App\Models\UserFollowing;
use App\Services\Cache\MediaCacheService;
use App\Services\MediaService;
use App\Services\UserFollowingService;
use App\Transformers\MediaTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserSubscriptionController extends Controller
{
    private UserFollowingService $userFollowingService;
    private Fractal $fractal;
    private UserTransformer $userTransformer;
    private MediaTransformer $mediaTransformer;
    private MediaService $mediaService;
    private MediaCacheService $mediaCacheService;

    public function __construct(
        Fractal $fractal,
        UserFollowingService $userFollowingService,
        UserTransformer $userTransformer,
        MediaService $mediaService,
        MediaTransformer $mediaTransformer,
        MediaCacheService $mediaCacheService,
    )
    {
        $this->userFollowingService = $userFollowingService;
        $this->fractal = $fractal;
        $this->userTransformer = $userTransformer;
        $this->mediaService = $mediaService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaCacheService = $mediaCacheService;
    }

    public function friends(Request $request): JsonResponse
    {
        $friends = $this->userFollowingService
            ->getFriendsQuery($request->user()->id)
            ->paginate($request->input('per_page'));
        $resource = new Collection($friends->items(), $this->userTransformer);
        $this->fractal->parseIncludes(['subscribers_count', 'subscriptions_count']);
        $resource->setPaginator(new IlluminatePaginatorAdapter($friends));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    /**
     * 粉丝列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function followers(Request $request): JsonResponse
    {
        $subscribers = $this->userFollowingService
            ->getFollowingUsersOfPublisherQuery(Auth::user()->id)
            ->paginate($request->input('per_page', 10));

        $resource = new Collection($subscribers->items(), $this->userTransformer);
        $this->fractal->parseIncludes(['subscribers_count', 'subscriptions_count', 'medias_count']);
        $resource->setPaginator(new IlluminatePaginatorAdapter($subscribers));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    /**
     * 用户关注列表
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function subscriptions(Request $request): JsonResponse
    {
        $subscribers = $this->userFollowingService
            ->getPublisherUsersOfFollowerQuery(Auth::user()->id)
            ->paginate($request->input('per_page'));
        $resource = new Collection($subscribers->items(), $this->userTransformer);
        $this->fractal->parseIncludes(['subscribers_count', 'subscriptions_count', 'medias_count']);
        $resource->setPaginator(new IlluminatePaginatorAdapter($subscribers));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function subscribe(int $publisherUserId): JsonResponse
    {
        $userId = Auth::user()->id;
        if (!User::find($publisherUserId)) {
            throw new IllegalArgumentException('user.id', 'publisher not found');
        }

        $redirect = $this->userFollowingService->getUserSubscriptionRedirect($userId, $publisherUserId);
        if ($redirect == null) {
            $this->userFollowingService->addSubscription(Auth::user()->id, $publisherUserId, null);
            return response()->json([
                'data' => [
                    'subscribed' => true,
                ],
            ]);
        }

        return response()->json([
            'data' => [
                'subscribed' => false,
                'registration_redirect' => $redirect == UserFollowing::USER_SUBSCRIBER_REDIRECT_REGISTRATION,
                'product_redirect' => $redirect == UserFollowing::USER_SUBSCRIBER_REDIRECT_PRODUCT,
            ],
        ], ResponseAlias::HTTP_FORBIDDEN);
    }

    public function deleteSubscription(int $publisherUserId): JsonResponse
    {

        if (!$this->userFollowingService->hasUserFollowedToPublisherUser(Auth::user()->id, $publisherUserId)) {
            return response()->json([
                'data' => [
                    'subscribed' => false
                ]
            ]);
        }

        $this->userFollowingService->deleteSubscription($publisherUserId, Auth::user()->id);

        return response()->json([
            'data' => [
                'subscribed' => false
            ]
        ]);
    }

    public function medias(Request $request): JsonResponse {
        $medias = $this->mediaService
            ->fetchSubscriptionMediasQuery($request->user()->id)
            ->paginate($request->input('per_page', 10));
        $resource = new Collection($medias->getCollection(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $request->user()->id);
        return response()->json($result);
    }
}
