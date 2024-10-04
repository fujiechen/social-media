<?php

namespace App\Services;

use App\Models\Meta;

class MetaService
{
    public function getValue(string $key): string {
        /**
         * @var Meta $meta
         */
        $meta = Meta::query()->where('key', '=', $key)->first();
        if ($meta) {
            return $meta->value;
        }

        return '';
    }
}
