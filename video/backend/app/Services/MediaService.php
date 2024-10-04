<?php

namespace App\Services;

use App\Dtos\BucketFileDto;
use App\Dtos\MediaDto;
use App\Dtos\MediaSearchDto;
use App\Dtos\PurchaseProductDto;
use App\Events\Actor\AddMediaActorViewCountEvent;
use App\Events\Actor\SyncActorActiveMediaAlbumCountEvent;
use App\Events\Actor\SyncActorActiveMediaSeriesCountEvent;
use App\Events\Actor\SyncActorActiveMediaVideoCountEvent;
use App\Events\Category\AddMediaCategoryViewCountEvent;
use App\Events\Category\SyncCategoryActiveMediaAlbumCountEvent;
use App\Events\Category\SyncCategoryActiveMediaSeriesCountEvent;
use App\Events\Category\SyncCategoryActiveMediaVideoCountEvent;
use App\Events\Media\AddMediaViewCountEvent;
use App\Events\Media\SyncMediaChildrenCountEvent;
use App\Events\Tag\AddMediaTagViewCountEvent;
use App\Events\Tag\SyncTagActiveMediaAlbumCountEvent;
use App\Events\Tag\SyncTagActiveMediaSeriesCountEvent;
use App\Events\Tag\SyncTagActiveMediaVideoCountEvent;
use App\Exceptions\IllegalArgumentException;
use App\Models\Album;
use App\Models\Media;
use App\Models\MediaPermission;
use App\Models\MediaRole;
use App\Models\Product;
use App\Models\Role;
use App\Models\Series;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class MediaService
{
    private ProductService $productService;
    private OrderService $orderService;
    private UserFollowingService $userFollowingService;
    private UserSearchService $userSearchService;

    public function __construct(ProductService       $productService,
                                OrderService         $orderService,
                                UserFollowingService $userFollowingService,
                                UserSearchService    $userSearchService)
    {
        $this->orderService = $orderService;
        $this->productService = $productService;
        $this->userFollowingService = $userFollowingService;
        $this->userSearchService = $userSearchService;
    }

    public function fetchMediaableModel(string $mediaableType, int $mediaableId): Model
    {
        return match ($mediaableType) {
            Video::class => Video::query()->find($mediaableId),
            Series::class => Series::query()->find($mediaableId),
            Album::class => Album::query()->find($mediaableId),
        };
    }

    public function updateOrCreateMedia(MediaDto $dto): Media
    {
        return DB::transaction(function () use ($dto) {
            if ($dto->mediaableType == Video::class) {
                $mediaableId = $dto->videoId;
            } else if ($dto->mediaableType == Series::class) {
                $mediaableId = $dto->seriesId;
            } else if ($dto->mediaableType == Album::class) {
                $mediaableId = $dto->albumId;
            }

            $mediaableModel = $this->fetchMediaableModel($dto->mediaableType, $mediaableId);

            if (empty($dto->name)) {
                $dto->name = $mediaableModel->name;
            }

            if (empty($dto->description)) {
                $dto->description = $mediaableModel->description;
            }

            //media update
            if ($dto->mediaId) {
                /**
                 * @var Media $oldMedia
                 */
                $oldMedia = Media::find($dto->mediaId);

                //delete roles
                MediaRole::query()
                    ->where('media_id', '=', $dto->mediaId)
                    ->delete();

                //delete roles of series children medias
                foreach ($oldMedia->childrenMedias as $childMedia) {
                    MediaRole::query()
                        ->where('media_id', '=', $childMedia->id)
                        ->delete();
                }

                //reset all permissions if media type changed
                MediaPermission::query()
                    ->where('media_id', '=', $dto->mediaId)
                    ->delete();

                //delete media product
                Product::query()
                    ->where('media_id', '=', $dto->mediaId);

                //delete series children media products
                foreach ($oldMedia->childrenMedias as $childMedia) {
                    Product::query()
                        ->where('media_id', '=', $childMedia->id);
                }
            }

            /**
             * @var Media $media
             */
            $media = Media::updateOrCreate([
                'id' => $dto->mediaId,
            ], [
                'name' => $dto->name,
                'description' => $dto->description,
                'mediaable_type' => $dto->mediaableType,
                'mediaable_id' => $mediaableId,
                'user_id' => $dto->userId,
                'parent_media_id' => $dto->parentMediaId,
                'status' => $dto->status,
            ]);

            if (in_array(Media::MEDIA_PERMISSION_ROLE, $dto->mediaPermissions)) {
                MediaPermission::query()->create([
                    'media_id' => $media->id,
                    'permission' => Media::MEDIA_PERMISSION_ROLE,
                ]);
                foreach ($dto->mediaRoleIds as $roleId) {
                    MediaRole::query()->create([
                        'media_id' => $media->id,
                        'role_id' => $roleId,
                    ]);
                }
            }

            if (in_array(Media::MEDIA_PERMISSION_PURCHASE, $dto->mediaPermissions)) {
                MediaPermission::query()->create([
                    'media_id' => $media->id,
                    'permission' => Media::MEDIA_PERMISSION_PURCHASE,
                ]);

                //check series media id
                $productName = $dto->name;
                $productDescription = $dto->description;

                if ($dto->parentMediaId != null) {
                    $productMediaId = $dto->parentMediaId;
                    $seriesMedia = Media::find($productMediaId);
                    $productName = $seriesMedia->name;
                    $productDescription = $seriesMedia->description;
                    // for child media product, use parent media thumbnail
                    $thumbnailFile = $seriesMedia->getThumbnailImage();
                } else {
                    $productMediaId = $media->id;

                    //use media thumbnail for product
                    $thumbnailFile = $media->getThumbnailImage();
                }

                $mediaProduct = $this->productService
                    ->findMediaProducts($productMediaId, $dto->status == Media::STATUS_ACTIVE)
                    ->first();

                $mediaProductId = 0;
                if ($mediaProduct) {
                    $mediaProductId = $mediaProduct->id;
                }

                $productDto = new PurchaseProductDto([
                    'productId' => $mediaProductId,
                    'thumbnailFileDto' => new BucketFileDto([
                        'bucketFilePath' => $thumbnailFile->bucket_file_path,
                        'bucketFileName' => $thumbnailFile->bucket_file_name,
                        'bucketName' => $thumbnailFile->bucket_name,
                        'bucketType' => $thumbnailFile->bucket_type,
                    ]),
                    'frequency' => Product::ONETIME,
                    'name' => $productName,
                    'description' => $productDescription,
                    'userId' => $dto->userId,
                    'mediaId' => $productMediaId,
                    'currencyName' => $dto->mediaProductCurrencyName,
                    'unitPrice' => $dto->mediaProductPrice,
                    'isActive' => $dto->status == Media::STATUS_ACTIVE
                ]);

                //no thumbnail and product images required
                $this->productService->updateOrCreateProduct($productDto);

            }

            if (in_array(Media::MEDIA_PERMISSION_SUBSCRIPTION, $dto->mediaPermissions)) {
                MediaPermission::query()->create([
                    'media_id' => $media->id,
                    'permission' => Media::MEDIA_PERMISSION_SUBSCRIPTION,
                ]);
            }

            //update or create video medias for series media
            if ($media->type == Media::TYPE_SERIES) {
                //delete media videos created before
                Media::query()->where('parent_media_id', '=', $media->id)->delete();

                /**
                 * @var Video $video
                 */
                foreach ($media->mediaable->videos as $video) {
                    $this->updateOrCreateMedia(new MediaDto([
                        'mediaId' => 0,
                        'userId' => $dto->userId,
                        'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
                        'videoId' => $video->id,
                        'name' => $video->name,
                        'description' => $video->description,
                        'mediaRoleIds' => $dto->mediaRoleIds,
                        'mediaProductCurrencyName' => $dto->mediaProductCurrencyName,
                        'mediaProductPrice' => $dto->mediaProductPrice,
                        'mediaPermissions' => $dto->mediaPermissions,
                        'parentMediaId' => $media->id,
                        'status' => $media->status,
                    ]));
                }

                /**
                 * @var Album $album
                 */
                foreach ($media->mediaable->albums as $album) {
                    $this->updateOrCreateMedia(new MediaDto([
                        'mediaId' => 0,
                        'userId' => $dto->userId,
                        'mediaableType' => Media::toMediaableType(Media::TYPE_ALBUM),
                        'albumId' => $album->id,
                        'name' => $album->name,
                        'description' => $album->description,
                        'mediaRoleIds' => $dto->mediaRoleIds,
                        'mediaProductCurrencyName' => $dto->mediaProductCurrencyName,
                        'mediaProductPrice' => $dto->mediaProductPrice,
                        'mediaPermissions' => $dto->mediaPermissions,
                        'parentMediaId' => $media->id,
                        'status' => $media->status,
                    ]));
                }
            }

            return $media;
        });
    }

    /**
     * Return similar medias based on input video
     * 1. if video is from series, load other videos id other than the input one
     * 2. load medias has the same actor
     * 3. load medias has the same tags
     * 3. load medias has the same categories
     *
     * @param int $videoMediaId
     * @param int $limit
     * @return Collection<Media[]>
     */
    public function fetchSimilarMediasByVideoMedia(int $videoMediaId, int $limit = 10): Collection
    {
        $similarMediaIds = [];

        /**
         * @var Media $media
         */
        $media = Media::find($videoMediaId);

        if ($media->parentMedia) {
            $similarMediaIds = $media->parentMedia->childrenMedias->pluck('id')->toArray();
        }

        $otherMediaIds = DB::table('media_searches as m1')
            ->select('m2.media_id')
            ->join('media_searches as m2', 'm1.search_text', '=', 'm2.search_text')
            ->where('m1.media_id', $videoMediaId)
            ->groupBy('m2.media_id')
            ->orderByRaw('COUNT(m2.search_text) DESC') // Order by search_text matches count
            ->pluck('m2.media_id')
            ->toArray();

        $similarMediaIds = array_merge($similarMediaIds, $otherMediaIds);

        $similarMediaIds = array_filter($similarMediaIds, function ($item) use ($videoMediaId) {
            return $item != $videoMediaId;
        });

        return Media::query()
            ->whereIn('id', $similarMediaIds)
            ->where('mediaable_type', '=', Media::toMediaableType(Media::TYPE_VIDEO))
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->whereNull('medias.deleted_at')
            ->limit($limit)
            ->get();
    }

    public function fetchAllMediasMetaQuery(array $mediaIds, ?int $userId = null): Builder {
        $query = Media::query()
            ->select('medias.*')
            ->distinct('medias.id')
            ->whereIn('medias.id', $mediaIds);

        if ($userId) {
            $query->leftJoin('media_favorites', function ($join) use ($userId) {
                $join->on('medias.id', '=', 'media_favorites.media_id')
                    ->where('media_favorites.user_id', '=', $userId);
            })
            ->leftJoin('media_likes', function ($join) use ($userId) {
                $join->on('medias.id', '=', 'media_likes.media_id')
                    ->where('media_likes.user_id', '=', $userId);
            })
            ->leftJoin('user_followings', function ($join) use ($userId) {
                $join->on('medias.user_id', '=', 'user_followings.publisher_user_id')
                    ->where('user_followings.following_user_id', '=', $userId);
            })
            ->addSelect([
                DB::raw('CASE WHEN media_favorites.user_id IS NOT NULL THEN 1 ELSE 0 END AS is_user_favorite'),
                DB::raw('CASE WHEN media_likes.user_id IS NOT NULL THEN 1 ELSE 0 END AS is_user_like'),
                DB::raw('CASE WHEN user_followings.following_user_id IS NOT NULL THEN 1 ELSE 0 END AS is_user_subscribe'),
            ]);
        } else {
            $query->addSelect([
                DB::raw('0 AS is_user_favorite'),
                DB::raw('0 AS is_user_like'),
                DB::raw('0 AS is_user_subscribe'),
            ]);
        }

        if(count($mediaIds) > 1) {
            $mediaIdsString = implode(',', $mediaIds);
            $query->orderByRaw(DB::raw("FIELD(medias.id, $mediaIdsString)"));
        }
        return $query;
    }

    /**
     * If $mediaSearchDto.user_id is set, we will check role of user
     * - visitor role only user, list medias with visitor role only medias
     * - other roles, can see all type of medias
     *
     * @param MediaSearchDto $mediaSearchDto
     * @return Builder
     */
    public function fetchAllMediasQuery(MediaSearchDto $mediaSearchDto): Builder
    {
        $query = Media::query()
            ->select('medias.*')
            ->distinct('medias.id')
            ->leftJoin('users', 'medias.user_id', '=', 'users.id')
            ->leftJoin('media_categories', 'medias.id', '=', 'media_categories.media_id')
            ->leftJoin('categories', 'categories.id', '=', 'media_categories.category_id')
            ->leftJoin('media_actors', 'medias.id', '=', 'media_actors.media_id')
            ->leftJoin('actors', 'actors.id', '=', 'media_actors.actor_id')
            ->leftJoin('media_tags', 'medias.id', '=', 'media_tags.media_id')
            ->leftJoin('tags', 'tags.id', '=', 'media_tags.tag_id')
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->whereNull('medias.deleted_at');

        if (!empty($mediaSearchDto->mediaId)) {
            $query->where('medias.id', $mediaSearchDto->mediaId);
        }

        if (!empty($mediaSearchDto->mediaableTypes)) {
            $query->whereIn('medias.mediaable_type', $mediaSearchDto->mediaableTypes);
        }

        if (!empty($mediaSearchDto->mediaableId)) {
            $query->where('medias.mediaable_id', '=', $mediaSearchDto->mediaableId);
        }

        if (!empty($mediaSearchDto->mediaUserId)) {
            $query->where('medias.user_id', '=', $mediaSearchDto->mediaUserId);
        }

        if (!empty($mediaSearchDto->nickName)) {
            $query->where('users.nickname', 'like', '%' . $mediaSearchDto->nickName . '%');
        }

        if (!empty($mediaSearchDto->actorId)) {
            $query->where('media_actors.actor_id', '=', $mediaSearchDto->actorId);
        }

        if (!empty($mediaSearchDto->actorName)) {
            $query->where('actors.name', 'like', '%' . $mediaSearchDto->actorName . '%');
        }

        if (!empty($mediaSearchDto->tagIds)) {
            $query->whereIn('media_tags.tag_id', $mediaSearchDto->tagIds);
        }

        if (!empty($mediaSearchDto->tagName)) {
            $query->where('tags.name', 'like', '%' . $mediaSearchDto->tagName . '%');
        }

        if (!empty($mediaSearchDto->categoryId)) {
            $query->where('media_categories.category_id', '=', $mediaSearchDto->categoryId);
        }

        if (!empty($mediaSearchDto->categoryName)) {
            $query->where('categories.name', 'like', '%' . $mediaSearchDto->categoryName . '%');
        }

        if (!empty($mediaSearchDto->mediaSearchText)) {
            $query->leftJoin('media_searches', 'medias.id', '=', 'media_searches.media_id');
            $query->where('media_searches.search_text', 'like', '%' . $mediaSearchDto->mediaSearchText . '%');
        }

        if (!empty($mediaSearchDto->orderBys)) {
            $orderBys = $mediaSearchDto->orderBys;

            if (in_array('likes', array_keys($orderBys))) {
                $query->withCount('mediaLikes')->orderBy('media_likes_count', $orderBys['likes']);
            }

            if (in_array('comments', array_keys($orderBys))) {
                $query->withCount('mediaComments')->orderBy('media_comments_count', $orderBys['comments']);
            }

            if (in_array('favorites', array_keys($orderBys))) {
                $query->withCount('mediaFavorites')->orderBy('media_favorites_count', $orderBys['favorites']);
            }

            if (in_array('id', array_keys($orderBys))) {
                $query->orderBy('medias.id', $orderBys['id']);
            }

        } else {
            $query->orderBy('medias.id', 'desc');
        }

        return $query;
    }


    /**
     * @param int $userId
     * @param int $mediaId
     * @return bool
     */
    public function isMediaAvailableToUser(int $userId, int $mediaId): bool
    {
        $mediaRedirects = $this->getMediaRedirectOfUser($userId, $mediaId);
        return $this->isMediaAvailableToRedirects($mediaRedirects);
    }

    public function isMediaAvailableToRedirects(array $mediaRedirects): bool {
        foreach($mediaRedirects as $permission => $redirect) {
            if (!is_null($redirect) && !$redirect) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function isMediaAvailableToDownload(?int $userId, int $mediaId): bool
    {
        if (empty($userId)) {
            return false;
        }

        if (!$this->isMediaAvailableToUser($userId, $mediaId)) {
            return false;
        }

        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        if ($media->permissions->contains(Media::MEDIA_PERMISSION_ROLE)
            || $media->permissions->contains(Media::MEDIA_PERMISSION_SUBSCRIPTION)) {
            /**
             * @var User $user
             */
            $user = User::find($userId);
            return $user->hasRole(Role::ROLE_MEMBERSHIP_ID);
        } else if ($media->permissions->contains(Media::MEDIA_PERMISSION_PURCHASE)) {
            return true;
        }

        return false;
    }

    /**
     *
     * check media permission
     * 1. role => media_roles include user_roles
     * 2. subscription => user subscribes to media owner
     * 3. purchase => find products product.media_id => order_products => order.status = completed
     *
     */
    #[ArrayShape([
        Media::MEDIA_REDIRECT_REGISTRATION => "bool|null",
        Media::MEDIA_REDIRECT_MEMBERSHIP => "bool|null",
        Media::MEDIA_REDIRECT_PRODUCT => "bool|null",
        Media::MEDIA_REDIRECT_SUBSCRIPTION => "bool|null"])
    ]
    public function getMediaRedirectOfUser(?int $userId, int $mediaId): array
    {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        $redirects = [
            Media::MEDIA_REDIRECT_REGISTRATION => null,
            Media::MEDIA_REDIRECT_MEMBERSHIP => null,
            Media::MEDIA_REDIRECT_PRODUCT => null,
            Media::MEDIA_REDIRECT_SUBSCRIPTION => null,
        ];


        if ($media->permissions->contains(Media::MEDIA_PERMISSION_ROLE)) {
            //check if a visitor needs to redirect to registration page
            if (empty($userId)) {
                if ($media->role_ids->contains(Role::ROLE_VISITOR_ID)) {
                    $redirects[Media::MEDIA_REDIRECT_REGISTRATION] = false;
                } elseif ($media->role_ids->contains(Role::ROLE_USER_ID)) {
                    $redirects[Media::MEDIA_REDIRECT_REGISTRATION] = true;
                }

                if ($media->role_ids->contains(Role::ROLE_MEMBERSHIP_ID)) {
                    $redirects[Media::MEDIA_REDIRECT_MEMBERSHIP] = true;
                }
            } else {
                //check for existing user has the role permission ready
                /**
                 * @var User $user
                 */
                $user = User::find($userId);

                $requireMembershipPermission = true;
                foreach ($media->role_ids as $roleId) {
                    if ($user->hasRole($roleId)) {
                        $requireMembershipPermission = false;
                    }
                }
                $redirects[Media::MEDIA_REDIRECT_MEMBERSHIP] = $requireMembershipPermission;
            }
        }

        // if the user has subscription permission
        if ($media->permissions->contains(Media::MEDIA_PERMISSION_SUBSCRIPTION)) {
            if (empty($userId)) {
                $redirects[Media::MEDIA_REDIRECT_SUBSCRIPTION] = true;
            } else {
                /**
                 * @var User $user
                 */
                $user = User::find($userId);

                if (!$this->userFollowingService->hasUserFollowedToPublisherUser($user->id, $media->user_id)) {
                    $redirects[Media::MEDIA_REDIRECT_SUBSCRIPTION] = true;
                } else {
                    $redirects[Media::MEDIA_REDIRECT_SUBSCRIPTION] = false;
                }
            }
        }

        // if the user has purchase permission
        if ($media->permissions->contains(Media::MEDIA_PERMISSION_PURCHASE)) {

            //for series use series media id to check product
            if ($media->parent_media_id != null) {
                $mediaId = $media->parent_media_id;
            }

            if (empty($userId)) {
                $redirects[Media::MEDIA_REDIRECT_PRODUCT] = true;
            } else {
                if (!$this->orderService->hasMediaProductBought($userId, $mediaId)) {
                    $redirects[Media::MEDIA_REDIRECT_PRODUCT] = true;
                } else {
                    $redirects[Media::MEDIA_REDIRECT_PRODUCT] = false;
                }
            }
        }

        return $redirects;
    }


    public function fetchAllMediasByAlbum(int $albumId): Collection
    {
        return Media::query()
            ->select('medias.*')
            ->distinct()
            ->where('mediaable_type', '=', Media::toMediaableType(Media::TYPE_ALBUM))
            ->where('mediaable_id', '=', $albumId)
            ->get();
    }

    public function fetchAllMediasByVideo(int $videoId): Collection
    {
        return Media::query()
            ->select('medias.*')
            ->distinct()
            ->where('mediaable_type', '=', Media::toMediaableType(Media::TYPE_VIDEO))
            ->where('mediaable_id', '=', $videoId)
            ->get();
    }

    public function fetchAllMediasBySeries(int $seriesId): Collection
    {
        return Media::query()
            ->select('medias.*')
            ->distinct()
            ->where('mediaable_type', '=', Media::toMediaableType(Media::TYPE_SERIES))
            ->where('mediaable_id', '=', $seriesId)
            ->get();
    }

    public function updateMediaSearchStatistics(MediaSearchDto $mediaSearchDto): void
    {
        if (!empty($mediaSearchDto->mediaSearchText) && !empty($mediaSearchDto->userId)) {
            $this->userSearchService->updateOrCreateUserSearch($mediaSearchDto->userId, $mediaSearchDto->mediaSearchText);
        }
    }

    public function fetchAllMediaUserQuery(): Builder
    {
        return User::query()
            ->select('users.*')
            ->join('medias', 'medias.user_id', '=', 'users.id')
            ->distinct('medias.user_id');
    }

    public function fetchAllMediaWithUserCommentQuery($userId): Builder
    {
        return Media::query()
            ->select('medias.*')
            ->distinct()
            ->join('media_comments', 'media_comments.media_id', '=', 'medias.id')
            ->where('media_comments.user_id', '=', $userId)
            ->orderBy('media_comments.id', 'desc');
    }

    public function fetchSubscriptionMediasQuery(int $userId): Builder
    {
        return Media::query()
            ->select('medias.*')
            ->join('user_followings', 'user_followings.publisher_user_id', '=', 'medias.user_id')
            ->where('user_followings.following_user_id', '=', $userId)
            ->whereNull('user_followings.deleted_at')
            ->orderBy('medias.id', 'desc');
    }

    public function updateMediaCount(Media $media): void
    {
        event(new AddMediaViewCountEvent($media));
        event(new AddMediaActorViewCountEvent($media));
        event(new AddMediaTagViewCountEvent($media));
        event(new AddMediaCategoryViewCountEvent($media));
    }

    public function postToggleActive(int $mediaId, int $status): void
    {
        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);
        if ($media->isSeries()) {
            foreach ($media->childrenMedias as $childMedia) {
                $childMedia->status = $status;
                $childMedia->save();
            }
        }

        $product = $media->mediaProduct();

        $isActive = $status == Media::STATUS_ACTIVE;

        if ($product) {
            $product->is_active = $isActive;
            $product->save();
        }

        //trigger sync active media count to tags, actors, categories and media.childrenCount
        if ($media->isVideo()) {
            event(new SyncTagActiveMediaVideoCountEvent($media, $isActive));
            event(new SyncCategoryActiveMediaVideoCountEvent($media, $isActive));
            event(new SyncActorActiveMediaVideoCountEvent($media, $isActive));
        } else if ($media->isAlbum()) {
            event(new SyncTagActiveMediaAlbumCountEvent($media, $isActive));
            event(new SyncCategoryActiveMediaAlbumCountEvent($media, $isActive));
            event(new SyncActorActiveMediaAlbumCountEvent($media, $isActive));
        } else if ($media->isSeries()) {
            event(new SyncTagActiveMediaSeriesCountEvent($media, $isActive));
            event(new SyncCategoryActiveMediaSeriesCountEvent($media, $isActive));
            event(new SyncActorActiveMediaSeriesCountEvent($media, $isActive));
            event(new SyncMediaChildrenCountEvent($media));
        }
    }

    public function postDeleted(Media $media): void
    {
        if ($product = $media->mediaProduct()) {
            $product->delete();
            DB::table('cache')
                ->where('key', 'like', 'media_%')
                ->delete();
        }
    }

    /**
     * This media has name, description, tags, actors, categories
     * @param Media $media
     * @return bool
     */
    public function isMediaReadyable(Media $media): bool {
        if (empty($media->getThumbnailImage())) {
            return false;
        }

        if (empty($media->name)) {
            return false;
        }

        if (empty($media->description)) {
            return false;
        }

        if (empty($media->mediaTags()->count())) {
            return false;
        }

        if (empty($media->mediaCategories()->count())) {
            return false;
        }

        if (empty($media->mediaActors()->count())) {
            return false;
        }

        return true;
    }
}
