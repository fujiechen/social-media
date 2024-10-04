<?php

namespace Tests;

use App\Dtos\AlbumDto;
use App\Dtos\MediaDto;
use App\Dtos\MembershipProductDto;
use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Dtos\ProductDto;
use App\Dtos\SeriesDto;
use App\Dtos\SubscriptionProductDto;
use App\Dtos\UploadFileDto;
use App\Dtos\UserDto;
use App\Dtos\VideoDto;
use App\Models\Album;
use App\Models\File;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use App\Models\Resource;
use App\Models\Role;
use App\Models\Series;
use App\Models\User;
use App\Models\Video;
use App\Services\AlbumService;
use App\Services\MediaService;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Services\SeriesService;
use App\Services\UserService;
use App\Services\VideoService;
use Faker\Generator;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static bool $setUpHasRunOnce = false;


    protected function setUp(): void {
        parent::setUp();
        if (!static::$setUpHasRunOnce) {
            Artisan::call('migrate:fresh');
            static::$setUpHasRunOnce = true;
        }
    }

    public function adminUser(): User {
        $user = User::find(1);
        if (!$user) {
            /**
             * @var UserService $userService
             */
            $userService = app(UserService::class);
            $user = $userService->createUser(new UserDto([
                'userId' => 1,
                'username' => $this->faker()->userName,
                'password' => $this->faker()->password,
                'nickname' => $this->faker()->firstName,
                'email' => $this->faker()->email,
            ]));
            $userService->updateUserAuth(new UserDto([
                'userId' => $user->id,
                'username' => $this->faker()->userName,
                'password' => $this->faker()->password,
                'nickname' => $this->faker()->firstName,
                'email' => $this->faker()->email,
                'roleIds' => [Role::ADMINISTRATOR_ID]
            ]));
        }

        return $user;
    }

    protected function makeService($service) {
        return $this->app->make($service);
    }

    protected function faker(): Generator {
        return $this->app->make('Faker\Generator');
    }

    protected function createVisitor(?int $userShareId = null): User {
        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        return $userService->createUser(new UserDto([
            'userId' => $this->faker()->numberBetween(2, 10000),
            'accessToken' => $this->faker()->text,
            'username' => $this->faker()->userName,
            'password' => $this->faker()->password,
            'nickname' => $this->faker()->firstName,
            'email' => $this->faker()->email,
            'userShareId' => $userShareId
        ]));
    }

    protected function createUser(?int $userShareId = null): User {
        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        $user = $this->createVisitor($userShareId);
        $userService->updateUserAuth(new UserDto([
            'userId' => $user->id,
            'accessToken' => $this->faker()->text,
            'username' => $this->faker()->userName,
            'password' => $this->faker()->password,
            'nickname' => $this->faker()->firstName,
            'email' => $this->faker()->email,
            'roleIds' => [Role::ROLE_USER_ID]
        ]));

        return $user;
    }

    protected function createResource(): Resource {
        return Resource::create([
            'name' => 'test'
        ]);
    }

    protected function createAlbum(): Album {
        /**
         * @var AlbumService $albumService
         */
        $albumService = app(AlbumService::class);
        return $albumService->updateOrCreateAlbum(new AlbumDto([
            'albumId' => 0,
            'type' => Album::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'imageFileDtos' => [
                new UploadFileDto([
                    'bucketType' => File::TYPE_LOCAL_BUCKET,
                    'uploadPath' => '',
                ]),
            ],
        ]));
    }

    protected function createVideo(): Video {
        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);
        return $videoService->updateOrCreateVideo(new VideoDto([
            'videoId' => 0,
            'type' => Video::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
        ]));
    }

    protected function createSeries(array $videoIds): Series {
        $videoDtos = [];
        foreach ($videoIds as $videoId) {
            $videoDtos[] = new VideoDto([
                'videoId' => $videoId,
                'type' => Video::TYPE_CLOUD,
            ]);
        }

        /**
         * @var SeriesService $seriesService
         */
        $seriesService = app(SeriesService::class);
        return $seriesService->updateOrCreateSeries(new SeriesDto([
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoDtos' => $videoDtos,
        ]));
    }

    protected function createMediaWithRolePermission(int $userId, array $roleIds): Media {

        $video = $this->createVideo();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        return $mediaService->updateOrCreateMedia(new MediaDto([
            'mediaId' => 0,
            'userId' => $userId,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'videoId' => $video->id,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'mediaRoleIds' => $roleIds,
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
        ]));
    }

    protected function createMediaWithSubscriptionPermission(int $userId): Media {

        $video = $this->createVideo();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        return $mediaService->updateOrCreateMedia(new MediaDto([
            'mediaId' => 0,
            'userId' => $userId,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'videoId' => $video->id,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'mediaPermission' => Media::MEDIA_PERMISSION_SUBSCRIPTION,
        ]));
    }

    protected function createMediaWithPurchasePermission(int $userId, float $price): Media {

        $video = $this->createVideo();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        return $mediaService->updateOrCreateMedia(new MediaDto([
            'mediaId' => 0,
            'userId' => $userId,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'videoId' => $video->id,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'mediaPermission' => Media::MEDIA_PERMISSION_PURCHASE,
            'mediaProductPrice' => $price,
            'mediaProductCurrencyName' => 'CNY',
        ]));
    }

    protected function createOrder(int $userId, int $productId, string $status): Order {
        /**
         * @var OrderService $orderService
         */
        $orderService = app(OrderService::class);
        return $orderService->updateOrCreateOrder(new OrderDto([
            'userId' => $userId,
            'status' => $status,
            'orderProductDtos' => [
                new OrderProductDto([
                    'productId' => $productId,
                    'qty' => 1,
                ]),
            ],
        ]));
    }

    protected function createMembershipProduct(string $currencyName, string $amount): Product {
        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        return $productService->updateOrCreateProduct(new MembershipProductDto([
            'productId' => 0,
            'type' => Product::TYPE_MEMBERSHIP,
            'name' => 'monthly membership',
            'description' => $this->faker()->text,
            'roleId' => Role::ROLE_MEMBERSHIP_ID,
            'currencyName' => $currencyName,
            'unitPrice' => $amount,
            'frequency' => Product::MONTHLY,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
        ]));
    }

    protected function createSubscriptionProduct(int $publisherUserId, string $currencyName, string $amount): Product {
        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        return $productService->updateOrCreateProduct(new SubscriptionProductDto([
            'productId' => 0,
            'userId' => $publisherUserId,
            'type' => Product::TYPE_SUBSCRIPTION,
            'name' => 'monthly subscription',
            'description' => $this->faker()->text,
            'currencyName' => $currencyName,
            'unitPrice' => $amount,
            'frequency' => Product::MONTHLY,
            'publisherUserId' => $publisherUserId,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
        ]));
    }

}
