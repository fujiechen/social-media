<?php

namespace Tests;

use App\Dtos\BucketFileDto;
use App\Dtos\CategoryDto;
use App\Dtos\FileDto;
use App\Dtos\ProductDto;
use App\Dtos\ServerDto;
use App\Dtos\UploadFileDto;
use App\Dtos\UserDto;
use App\Models\Category;
use App\Models\File;
use App\Models\Product;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\FileService;
use App\Services\ProductService;
use App\Services\ServerService;
use App\Services\UserService;
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

    private function createFileDto(): FileDto {
        return new BucketFileDto([
            'bucketType' => File::TYPE_LOCAL_BUCKET,
            'bucketFilePath' => $this->faker()->filePath(),
            'bucketName' => $this->faker()->name,
            'bucketFileName' => $this->faker()->name,
        ]);
    }

    protected function createUser(?int $userShareId = null): User {
        /**
         * @var UserService $userService
         */
        $userService = app(UserService::class);
        return $userService->createUser(new UserDto([
            'userId' => $this->faker()->numberBetween(2, 100),
            'accessToken' => $this->faker()->text,
            'username' => $this->faker()->userName,
            'password' => $this->faker()->password,
            'nickname' => $this->faker()->firstName,
            'email' => $this->faker()->email,
            'userShareId' => $userShareId
        ]));
    }

    protected function createCategory(): Category {
        /**
         * @var CategoryService $categoryService
         */
        $categoryService = app(CategoryService::class);
        return $categoryService->updateOrCreateCategory(new CategoryDto([
            'name' => $this->faker()->name,
            'description' => $this->faker()->name,
            'tags' => [$this->faker()->text],
            'highlights' => [$this->faker()->text],
            'thumbnailFileDto' => $this->createFileDto(),
        ]));
    }

    protected function createServer(int $categoryId): Server {
        /**
         * @var ServerService $serverService
         */
        $serverService = app(ServerService::class);
        return $serverService->updateOrCreateServer(new ServerDto([
            'name' => $this->faker()->name,
            'type' => Server::TYPE_IPSEC,
            'ip' => $this->faker()->ipv4,
            'countryCode' => $this->faker()->countryCode,
            'categoryId' => $categoryId,
        ]));
    }

    protected function createProduct(int $categoryId, string $frequency, float $amount): Product {
        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        return $productService->updateOrCreateProduct(new ProductDto([
            'productId' => 0,
            'name' => $frequency,
            'description' => $this->faker()->text,
            'currencyName' => 'CNY',
            'unitPrice' => $amount,
            'frequency' => $frequency,
            'categoryId' => $categoryId,
            'thumbnailFileDto' => $this->createFileDto(),
        ]));
    }
}
