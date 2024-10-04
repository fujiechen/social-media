<?php

namespace App\Http\Controllers;

use App\Dtos\MediaSearchDto;
use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\Media\SearchMediaRequest;
use App\Models\Media;
use App\Models\User;
use App\Services\ActorService;
use App\Services\Cache\ActorCacheService;
use App\Services\Cache\CategoryCacheService;
use App\Services\Cache\MediaCacheService;
use App\Services\Cache\TagCacheService;
use App\Services\CategoryService;
use App\Services\MediaHistoryService;
use App\Services\MediaRecommendationService;
use App\Services\MediaService;
use App\Services\TagService;
use App\Services\UserService;
use App\Transformers\ActorTransformer;
use App\Transformers\CategoryTransformer;
use App\Transformers\MediaMetaTransformer;
use App\Transformers\MediaRedirectTransformer;
use App\Transformers\MediaTransformer;
use App\Transformers\TagTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class MediaController extends Controller
{
    private Fractal $fractal;
    private MediaService $mediaService;
    private MediaHistoryService $mediaHistoryService;
    private TagTransformer $tagTransformer;
    private ActorTransformer $actorTransformer;
    private CategoryTransformer $categoryTransformer;
    private UserTransformer $userTransformer;
    private ActorService $actorService;
    private TagService $tagService;
    private CategoryService $categoryService;
    private UserService $userService;
    private MediaCacheService $mediaCacheService;
    private TagCacheService $tagCacheService;
    private CategoryCacheService $categoryCacheService;
    private ActorCacheService $actorCacheService;
    private MediaRedirectTransformer $mediaRedirectTransformer;
    private MediaMetaTransformer $mediaMetaTransformer;
    private MediaRecommendationService $mediaRecommendationService;
    private MediaTransformer $mediaTransformer;

    public function __construct(Fractal             $fractal,
                                MediaService        $mediaService,
                                MediaHistoryService $mediaHistoryService,
                                TagTransformer      $tagTransformer,
                                ActorTransformer    $actorTransformer,
                                CategoryTransformer $categoryTransformer,
                                UserTransformer     $userTransformer,
                                ActorService        $actorService,
                                TagService          $tagService,
                                CategoryService     $categoryService,
                                UserService         $userService,
                                MediaCacheService   $mediaCacheService,
                                TagCacheService     $tagCacheService,
                                CategoryCacheService $categoryCacheService,
                                ActorCacheService $actorCacheService,
                                MediaTransformer $mediaTransformer,
                                MediaRedirectTransformer $mediaRedirectTransformer,
                                MediaMetaTransformer $mediaMetaTransformer,
                                MediaRecommendationService $mediaRecommendationService)
    {
        $this->fractal = $fractal;
        $this->mediaService = $mediaService;
        $this->mediaHistoryService = $mediaHistoryService;
        $this->tagTransformer = $tagTransformer;
        $this->categoryTransformer = $categoryTransformer;
        $this->actorTransformer = $actorTransformer;
        $this->userTransformer = $userTransformer;
        $this->actorService = $actorService;
        $this->tagService = $tagService;
        $this->categoryService = $categoryService;
        $this->userService = $userService;
        $this->mediaCacheService = $mediaCacheService;
        $this->tagCacheService = $tagCacheService;
        $this->categoryCacheService = $categoryCacheService;
        $this->actorCacheService = $actorCacheService;
        $this->mediaTransformer = $mediaTransformer;
        $this->mediaRedirectTransformer = $mediaRedirectTransformer;
        $this->mediaMetaTransformer = $mediaMetaTransformer;
        $this->mediaRecommendationService = $mediaRecommendationService;
    }

    public function index(SearchMediaRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $this->mediaService->updateMediaSearchStatistics($dto);

        $result = $this->mediaCacheService->getOrCreateMediaSearchList($dto, $perPage, $page);

        return response()->json($result);
    }

    public function recommendation(Request $request): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = null;
        $userId = null;
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $userId = $user->id;
        }

        $perPage = $request->input('per_page', 10);

        $medias = $this->mediaRecommendationService
            ->fetchAllMediasWithUserRecommendation($user)
            ->paginate($perPage);

        $resource = new Collection($medias->getCollection(), $this->mediaTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($medias));
        $mediasArray = $this->fractal->createData($resource)->toArray();
        $result = $this->mediaCacheService->appendListMediaMeta($mediasArray, $userId);
        return response()->json($result);
    }

    public function tags(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $result = $this->tagCacheService->getOrCreateTagSearchList($name, $perPage, $page);
        return response()->json($result);
    }

    public function tag(int $tagId): JsonResponse
    {
        $tag = $this->tagService->getTagAndIncreaseCount($tagId);
        $resource = new Item($tag, $this->tagTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function categories(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $result = $this->categoryCacheService->getOrCreateCategorySearchList($name, $perPage, $page);
        return response()->json($result);
    }

    public function category(int $categoryId): JsonResponse
    {
        $category = $this->categoryService->getCategoryAndIncreaseCount($categoryId);
        $resource = new Item($category, $this->categoryTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function actors(Request $request): JsonResponse
    {
        $name = $request->input('name');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $result = $this->actorCacheService->getOrCreateActorSearchList($name, $perPage, $page);
        return response()->json($result);
    }

    public function actor(int $actorId): JsonResponse
    {
        $actor = $this->actorService->getActorAndIncreaseCount($actorId);
        $resource = new Item($actor, $this->actorTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function users(Request $request): JsonResponse
    {
        $users = $this->mediaService->fetchAllMediaUserQuery();
        if ($name = $request->input('name')) {
            $users->where('nickname', 'like', '%' . $name . '%');
        }
        $users = $users->orderBy('priority', 'desc');
        $users = $users->orderBy('id');
        $users = $users->paginate($request->input('per_page', 10));
        $this->fractal->parseIncludes(['subscriptions_count', 'medias_count']);
        $resource = new Collection($users->getCollection(), $this->userTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($users));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function user(int $userId): JsonResponse
    {
        $user = $this->userService->getUserAndIncreaseCount($userId);
        $this->fractal->parseIncludes(['subscriptions_count', 'medias_count']);
        $resource = new Item($user, $this->userTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    /**
     * @throws IllegalArgumentException
     */
    public function show(int $mediaId): JsonResponse
    {
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $userId = $user->id;
        } else {
            $userId = null;
        }

        /**
         * @var Media $media
         */
        $media = $this->mediaService
            ->fetchAllMediasQuery(
                new MediaSearchDto([
                    'mediaId' => $mediaId,
                    'userId' => $userId,
                ]))
            ->first();

        if (empty($media)) {
            throw new IllegalArgumentException('status', 'Media is not found');
        }

        if ($userId) {
            $this->mediaHistoryService->createMediaHistory($userId, $mediaId);
        }

        //add media count to queue
        $this->mediaService->updateMediaCount($media);

        $mediaRedirects = $this->mediaService->getMediaRedirectOfUser($userId, $mediaId);
        $isMediaAvailableToUser = $this->mediaService->isMediaAvailableToRedirects($mediaRedirects);

        $includes = [];
        if ($isMediaAvailableToUser) {
            $includes[] = 'media_file';
        }

        $mediaDownloadable = $this->mediaService->isMediaAvailableToDownload($userId, $mediaId);
        if ($mediaDownloadable) {
            $includes[] = 'download_file';
        }

        $mediaArray = $this->mediaCacheService->getOrCreateMediaShow($media, $includes);

        //merge in redirect transformer
        $mediaRedirectResource = new Item($media, $this->mediaRedirectTransformer);
        $this->fractal->parseIncludes($includes);
        $mediaRedirectArray = $this->fractal->createData($mediaRedirectResource)->toArray();
        $mediaArray['data'] = array_merge_recursive($mediaArray['data'], $mediaRedirectArray['data']);

        //merge in meta transformer
        $mediaMeta = $this->mediaService->fetchAllMediasMetaQuery([$mediaId], $userId)->first();
        $mediaMetaResource = new Item($mediaMeta, $this->mediaMetaTransformer);
        $mediaMetaArray = $this->fractal->createData($mediaMetaResource)->toArray();
        $mediaArray['data'] = array_merge_recursive($mediaArray['data'], $mediaMetaArray['data']);

        foreach ($mediaRedirects as $permission => $redirect) {
            $mediaArray['data']['meta']['user']['redirects'][$permission] = $redirect;
        }
        $mediaArray['data']['meta']['user']['redirects']['is_available'] = $isMediaAvailableToUser;

        return response()->json($mediaArray);
    }

    public function similar(Request $request, int $mediaId): JsonResponse
    {
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $userId = $user->id;
        } else {
            $userId = null;
        }

        $limit = $request->input('limit', 10);
        $result = $this->mediaCacheService->getOrCreateMediaSimilarList($mediaId, $limit, $userId);

        return response()->json($result);
    }
}
