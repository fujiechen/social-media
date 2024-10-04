<?php

namespace App\Transformers;

use App\Models\Meta;
use App\Models\UserShare;
use App\Services\MetaService;
use League\Fractal\TransformerAbstract;

class UserShareTransformer extends TransformerAbstract
{
    private MetaService $metaService;

    public function __construct(MetaService $metaService) {
        $this->metaService = $metaService;
    }

    public function transform(UserShare $userShare): array
    {
        return [
            'id' => $userShare->id,
            'type' => $userShare->shareable_type ? UserShare::toType($userShare->shareable_type) : null,
            'created_at_formatted' => $userShare->created_at_formatted,
            'updated_at_formatted' => $userShare->updated_at_formatted,
            'shareable_id' => $userShare->shareable_id,
            'share_url' => $userShare->url . '?user_share_id=' . $userShare->id,
            'qr_code_image_url' => url('api/user/shares/' . $userShare->id . '/qrCode'),
            'share_text' => $this->metaService->getValue(Meta::SHARE_TEXT),
            'background_image' => [
                'url' => $this->metaService->getValue(Meta::SHARE_IMAGE_URL)
            ]
        ];
    }

}
