<?php

namespace App\Utils;

use Illuminate\Support\Str;

class Utilities
{
    public static function formatCurrency(string $currencyName, int $amountCents): string {
        if ($currencyName === 'CNY') {
            return '¥' . number_format($amountCents / 100, 2);
        }

        return number_format($amountCents / 100, 2);
    }
}
