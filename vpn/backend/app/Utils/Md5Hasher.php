<?php

namespace App\Utils;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Hashing\AbstractHasher;

class Md5Hasher extends AbstractHasher implements Hasher
{
    public function make($value, array $options = []): string
    {
        return md5($value);
    }

    public function check($value, $hashedValue, array $options = []): bool
    {
        return $this->make($value) === $hashedValue;
    }

    public function needsRehash($hashedValue, array $options = []): bool
    {
        return false;
    }
}
