<?php

namespace App\Services;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PostService
{
    public function fetchPostQuery(int $accountId, Carbon $from, Carbon $to): Builder {
        return Post::where('account_id', '=', $accountId)
            ->whereBetween('created_at', [$from, $to]);
    }

    public function createDraftPost(string $instruction, int $accountId): Post {
        return Post::create([
            'instruction' => $instruction,
            'account_id' => $accountId,
            'status' => Post::STATUS_DRAFT,
        ]);
    }
}
