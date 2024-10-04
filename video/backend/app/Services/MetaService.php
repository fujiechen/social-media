<?php

namespace App\Services;

use App\Models\Meta;

class MetaService
{
    public function getValue(string $key, string $default = ''): string {
        /**
         * @var Meta $meta
         */
        $meta = Meta::query()->where('meta_key', '=', $key)->first();
        if ($meta) {
            return $meta->meta_value;
        }

        return $default;
    }
}
