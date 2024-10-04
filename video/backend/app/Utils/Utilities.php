<?php

namespace App\Utils;

class Utilities
{
    public static function formatCurrency(string $currencyName, int $amountCents): string {
        if ($currencyName === 'CNY') {
            return '¥' . number_format($amountCents / 100, 2);
        }

        if ($currencyName === 'COIN') {
            return 'C' . number_format($amountCents / 100, 2);
        }

        return number_format($amountCents / 100, 2);
    }
}
