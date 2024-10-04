<?php

namespace App\Services;

use App\Dtos\MediaProductSearchDto;
use App\Dtos\ProductDto;
use App\Dtos\ProductSearchDto;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function fetchMediaProductsQuery(MediaProductSearchDto $mediaProductSearchDto): Builder
    {
        $mediaId = $mediaProductSearchDto->mediaId;

        /**
         * @var Media $media
         */
        $media = Media::find($mediaId);

        // user parent series media if it's children media
        if ($media->parent_media_id) {
            $media = $media->parentMedia;
            $mediaId = $media->id;
            if (!$media->is_active) {
                return Product::query()->whereRaw('1 = 0');
            }
        }

        $memberShipProductsQuery = null;
        $subscriptionProductsQuery = null;
        $mediaProductsQuery = null;

        // check membership products
        if ($media->permissions->contains(Media::MEDIA_PERMISSION_ROLE)
            && $media->role_ids->contains(Role::ROLE_MEMBERSHIP_ID)) {
            $memberShipProductsQuery = Product::query()
                ->select('products.*')
                ->where('role_id', '=', Role::ROLE_MEMBERSHIP_ID)
                ->where('products.is_active', '=', true)
                ->distinct();
        }

        // check subscription products
        if ($media->permissions->contains(Media::MEDIA_PERMISSION_SUBSCRIPTION)
            && !empty($media->user_id)) {
            $subscriptionProductsQuery = Product::query()
                ->select('products.*')
                ->where('publisher_user_id', '=', $media->user_id)
                ->where('products.is_active', '=', true)
                ->distinct();
        }

        // check media products
        if ($media->permissions->contains(Media::MEDIA_PERMISSION_PURCHASE)) {
            $mediaProductsQuery = Product::query()
                ->select('products.*')
                ->where('products.is_active', '=', true)
                ->where('media_id', '=', $mediaId)
                ->distinct();
        }

        // Start with an empty query
        $query = Product::query()->select('products.*')->whereRaw('1 = 0');

        // Union the queries if they exist
        if ($memberShipProductsQuery) {
            $query = $query->union($memberShipProductsQuery);
        }
        if ($subscriptionProductsQuery) {
            $query = $query->union($subscriptionProductsQuery);
        }
        if ($mediaProductsQuery) {
            $query = $query->union($mediaProductsQuery);
        }

        return $query;
    }

    public function fetchAllProductsQuery(ProductSearchDto $productSearchDto): Builder
    {
        $query = Product::query()
            ->select('products.*')
            ->distinct('products.id')
            ->where('products.is_active', '=', true);

        if ($productUserType = $productSearchDto->productUserType) {
            if ($productUserType == 'self') {
                $query->whereNull('user_id');
            } else if ($productUserType == 'user') {
                $query->whereNotNull('user_id');
            }
        }

        if ($productSearchDto->mediaId) {
            /**
             * @var Media $media
             */
            $media = Media::find($productSearchDto->mediaId);

            if ($media->status != Media::STATUS_ACTIVE) {
                return Product::query()->whereRaw('1 = 0');
            }

            if ($media->parent_media_id) {
                $mediaId = $media->parent_media_id;

                if (!$media->parentMedia->is_active) {
                    return Product::query()->whereRaw('1 = 0');
                }

            } else {
                $mediaId = $media->id;
            }

            $query->where('media_id', '=', $mediaId);
        }

        if ($productSearchDto->userId) {
            $query->where('user_id', '=', $productSearchDto->userId);
        }

        if ($type = $productSearchDto->type) {
            if ($type == Product::TYPE_MEDIA) {
                $query->whereNotNull('media_id');
            } else if ($type == Product::TYPE_MEMBERSHIP) {
                $query->whereNotNull('role_id');
            } else if ($type == Product::TYPE_SUBSCRIPTION) {
                $query->whereNotNull('publisher_user_id');
            }
        }

        if ($productSearchDto->currencyName) {
            $query->where('currency_name', '=', $productSearchDto->currencyName);
        }

        return $query;
    }

    public function updateOrCreateProduct(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $thumbnailFileId = $this->fileService->getOrCreateFile($dto->thumbnailFileDto)->id;

            $product = Product::updateOrCreate([
                'id' => $dto->productId
            ], [
                'name' => $dto->name,
                'order_num_allowance' => empty($dto->orderNumAllowance) ? null : $dto->orderNumAllowance,
                'description' => $dto->description,
                'user_id' => $dto->userId,
                'currency_name' => $dto->currencyName,
                'unit_cents' => $dto->unitPrice * 100,
                'frequency' => $dto->frequency,
                'thumbnail_file_id' => $thumbnailFileId,
                'publisher_user_id' => $dto->publisherUserId ?? null,
                'role_id' => $dto?->roleId ?? null,
                'media_id' => $dto?->mediaId ?? null,
                'is_active' => $dto->isActive,
            ]);

            ProductImage::query()->where('product_id', '=', $product->id)->delete();

            foreach ($dto->imageFileDtos as $imageFileDto) {
                $imageFileId = $this->fileService->getOrCreateFile($imageFileDto)->id;

                ProductImage::create([
                    'product_id' => $product->id,
                    'file_id' => $imageFileId
                ]);
            }

            return $product;
        });
    }

    public function findSubscriptionProducts(int $publisherUserId, ?string $frequency = null, ?string $search = null): Builder
    {
        return $this->fetchProductsQuery(true, null, null, $frequency, $publisherUserId, null, $search);
    }

    public function findMediaProducts(int $mediaId, bool $isActive, ?string $search = null): Builder
    {
        return $this->fetchProductsQuery($isActive, null, $mediaId, null, null, $search);
    }

    private function fetchProductsQuery($isActive = true, ?int $roleId = null, ?int $mediaId = null, ?string $frequency = null, ?int $publisherUserId = null, ?int $userId = null, ?string $search = null): Builder
    {
        $query = Product::query()
            ->select('products.*')
            ->where('products.is_active', '=', $isActive);

        if (!empty($roleId)) {
            $query->where('role_id', '=', $roleId);
        }

        if (!empty($mediaId)) {
            $query->where('media_id', '=', $mediaId);
        }

        if ($frequency) {
            $query->where('frequency', '=', $frequency);
        }

        if (!empty($publisherUserId)) {
            $query->where('publisher_user_id', '=', $publisherUserId);
        }

        if (!empty($userId)) {
            $query->where('user_id', '=', $userId);
        }

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query;
    }

    public function postDeleted(Product $product): void {
        DB::table('cache')
            ->where('key', 'like',  'product_%')
            ->delete();
    }
}
