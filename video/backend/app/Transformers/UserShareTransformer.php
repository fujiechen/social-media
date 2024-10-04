<?php

namespace App\Transformers;

use App\Models\Media;
use App\Models\Meta;
use App\Models\Product;
use App\Models\UserShare;
use App\Services\MetaService;
use League\Fractal\TransformerAbstract;

class UserShareTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private MetaService $metaService;

    public function __construct(MetaService $metaService, FileTransformer $fileTransformer)
    {
        $this->metaService = $metaService;
        $this->fileTransformer = $fileTransformer;
    }

    public function transform(?UserShare $userShare): array
    {
        if (empty($userShare)) {
            return [];
        }

        $shareText = $this->metaService->getValue(Meta::SHARE_TEXT);
        $shareBackgroundImage = [
            'url' => $this->metaService->getValue(Meta::SHARE_IMAGE_URL)
        ];

        if ($userShare->isMedia()) {
            /**
             * @var Media $media
             */
            $media = $userShare->shareable;
            $shareText = $media->name;
            $shareBackgroundImage = $this->fileTransformer->transform($media->getThumbnailImage());
        } else if ($userShare->isProduct()) {
            /**
             * @var Product $product
             */
            $product = $userShare->shareable;
            $shareText = $product->name;
            $shareBackgroundImage = $this->fileTransformer->transform($product->thumbnailFile);
        }

        return [
            'id' => $userShare->id,
            'type' => $userShare->shareable_type ? UserShare::toType($userShare->shareable_type) : null,
            'created_at_formatted' => $userShare->created_at_formatted,
            'shareable_id' => $userShare->shareable_id,
            'share_url' => $userShare->url . '?user_share_id=' . $userShare->id,
            'qr_code_image_url' => url('api/user/shares/' . $userShare->id . '/qrCode'),
            'share_text' => $shareText,
            'background_image' => $shareBackgroundImage
        ];
    }

}
