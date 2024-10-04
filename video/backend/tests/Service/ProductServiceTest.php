<?php

use App\Dtos\MembershipProductDto;
use App\Dtos\ProductSearchDto;
use App\Dtos\SubscriptionProductDto;
use App\Dtos\UploadFileDto;
use App\Models\File;
use App\Models\Media;
use App\Models\Product;
use App\Models\Role;
use App\Services\ProductService;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    public function testFetchAllProductsQueryOfRoleProducts(): void {
        Product::query()->delete();
        $roleMedia = $this->createMediaWithRolePermission($this->adminUser()->id, [Role::ROLE_MEMBERSHIP_ID]);

        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        $product = $productService->updateOrCreateProduct(new MembershipProductDto([
            'productId' => 0,
            'userId' => $this->adminUser()->id,
            'type' => Product::TYPE_MEMBERSHIP,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'name' => $roleMedia->name,
            'description' => $roleMedia->description,
            'roleId' => Role::ROLE_MEMBERSHIP_ID,
            'currencyName' => 'CNY',
            'unitPrice' => 11.11,
            'frequency' => Product::MONTHLY,
        ]));

        $productsQuery = $productService->fetchAllProductsQuery(new ProductSearchDto([
            'type' => Product::TYPE_MEMBERSHIP,
        ]));

        $this->assertEquals(1, $productsQuery->count());
        $this->assertEquals($product->id, $productsQuery->first()->id);
    }

    public function testFetchAllProductsQueryOfSubscriptionProducts(): void {
        Product::query()->delete();
        $media = $this->createMediaWithSubscriptionPermission($this->adminUser()->id);

        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        $product = $productService->updateOrCreateProduct(new SubscriptionProductDto([
            'productId' => 0,
            'userId' => $this->adminUser()->id,
            'publisherUserId' => $this->adminUser()->id,
            'type' => Product::TYPE_SUBSCRIPTION,
            'name' => $media->name,
            'description' => $media->description,
            'currencyName' => 'CNY',
            'unitPrice' => 11.11,
            'frequency' => Product::MONTHLY,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $productsQuery = $productService->fetchAllProductsQuery(new ProductSearchDto([
            'type' => Product::TYPE_SUBSCRIPTION,
        ]));

        $this->assertEquals(1, $productsQuery->count());
        $this->assertEquals($product->id, $productsQuery->first()->id);
    }

    public function testFetchAllProductsQueryOfPurchaseProducts(): void {
        Product::query()->delete();

        $media = $this->createMediaWithPurchasePermission($this->adminUser()->id, 11.11);

        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);
        $productsQuery = $productService->fetchAllProductsQuery(new ProductSearchDto([
            'type' => Product::TYPE_MEDIA,
            'mediaId' => $media->id,
        ]));

        $this->assertEquals(1, $productsQuery->count());
        $this->assertEquals($media->mediaProduct()->id, $productsQuery->first()->id);
    }
}
