<?php

namespace App\Console\Commands;

use App\Dtos\BucketFileDto;
use App\Dtos\MediaSearchDto;
use App\Dtos\ProductDto;
use App\Models\File;
use App\Models\Media;
use App\Models\MediaPermission;
use App\Models\MediaRole;
use App\Models\Product;
use App\Models\Role;
use App\Services\MediaService;
use App\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetMediaPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:reset-media-permissions';

    protected $description = 'Reset Media Permissions';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Log::info('start reset media permissions');

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        /**
         * @var ProductService $productService
         */
        $productService = app(ProductService::class);


        /**
         * find all active medias
         * if media product already exist, skip this, otherwise
         *  - remove all media permissions
         *  - create 30 points media product
         *  - create 3 rmb media product
         *  - add membership permission only
         */
        $mediaService->fetchAllMediasQuery(new MediaSearchDto([]))
            ->chunk(100, function ($medias) use ($productService) {
                /**
                 * @var Media $media
                 */
                foreach ($medias as $media) {
//                    if ($media->permissions->contains(Media::MEDIA_PERMISSION_PURCHASE)) {
//                        Log::info('skip media ' . $media->id);
//                        continue;
//                    }

                    if (empty($media->getThumbnailImage())) {
                        Log::info('skip media without thumbnail  ' . $media->id);
                        continue;
                    }

                    Log::info('reset media permission and products: ' . $media->id);

                    // delete from media permissions
                    MediaPermission::query()->where('media_id', '=', $media->id)->delete();

                    // create membership permission
                    MediaPermission::query()->create([
                        'media_id' => $media->id,
                        'permission' => Media::MEDIA_PERMISSION_ROLE
                    ]);

                    MediaPermission::query()->create([
                        'media_id' => $media->id,
                        'permission' => Media::MEDIA_PERMISSION_PURCHASE
                    ]);

                    // delete from media roles
                    MediaRole::query()->where('media_id', '=', $media->id)->delete();

                    MediaRole::query()->create([
                        'media_id' => $media->id,
                        'role_id' => Role::ROLE_MEMBERSHIP_ID,
                    ]);

                    //delete media products
                    Product::query()->where('media_id', '=', $media->id)->delete();

                    // create 30 point media product
                    $coinProductDto = ProductDto::create([
                        'name' => $media->name,
                        'description' => $media->description,
                        'type' => Product::TYPE_MEDIA,
                        'currencyName' => 'COIN',
                        'unitPrice' => 30,
                        'userId' => $media->user_id,
                        'mediaId' => $media->id,
                        'thumbnailFileDto' => new BucketFileDto([
                            'fileId' =>  $media->getThumbnailImage()->id,
                            'bucketType' => File::TYPE_PUBLIC_BUCKET,
                        ])
                    ]);
                    $productService->updateOrCreateProduct($coinProductDto);

                    // create 3 rmb media product
                    $cashProductDto = ProductDto::create([
                        'name' => $media->name,
                        'description' => $media->description,
                        'type' => Product::TYPE_MEDIA,
                        'currencyName' => 'CNY',
                        'unitPrice' => 3,
                        'userId' => $media->user_id,
                        'mediaId' => $media->id,
                        'thumbnailFileDto' => new BucketFileDto([
                            'fileId' =>  $media->getThumbnailImage()->id,
                            'bucketType' => File::TYPE_PUBLIC_BUCKET,
                        ])
                    ]);
                    $productService->updateOrCreateProduct($cashProductDto);
                }
            });

        Log::info('completed reset media permissions');

    }
}
