<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\DB;

class CacheService
{
    public function deleteCacheByPrefix(string $prefix): void {
        DB::table('cache')
            ->where('key', 'like',  config('cache.prefix') . $prefix . '%')
            ->delete();
    }
}
