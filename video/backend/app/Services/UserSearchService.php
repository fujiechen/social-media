<?php

namespace App\Services;

use App\Models\UserSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserSearchService
{
    public function updateOrCreateUserSearch(int $userId, string $search): ?UserSearch {

        if (empty($search)) {
            return null;
        }

        return DB::transaction(function() use ($userId, $search) {
            return UserSearch::query()->create([
                'user_id' => $userId,
                'search' => $search,
            ]);
        });
    }

    public function getUserSearchesHistoryQuery(int $userId): Builder {
        return UserSearch::query()
            ->select('search', DB::raw('COUNT(search) as search_count'))
            ->groupBy('search')
            ->where('user_id', '=', $userId)
            ->orderBy('id', 'desc');
    }

    public function getHotUserSearchesQuery(): Builder {
        return UserSearch::query()
            ->select('search', DB::raw('COUNT(search) as search_count'))
            ->groupBy('search')
            ->orderBy('search_count', 'desc');
    }

}
