<?php

namespace App\Transformers;

use App\Models\MediaComment;
use League\Fractal\TransformerAbstract;

class MediaCommentTransformer extends TransformerAbstract
{
    private UserTransformer $userTransformer;

    public function __construct(UserTransformer $userTransformer) {
        $this->userTransformer = $userTransformer;
    }

    public function transform(?MediaComment $mediaComment): array
    {
        if (!$mediaComment) {
            return [];
        }

        return [
            'id' => $mediaComment->id,
            'comment' => $mediaComment->comment,
            'deleted_at' => $mediaComment->deleted_at,
            'created_at' => $mediaComment->created_at,
            'user' => $this->userTransformer->transform($mediaComment->user)
        ];
    }

}
