<?php

namespace App\Utils;

use Carbon\Carbon;

class Formatter
{
    public const TREND_UP = 'up';
    public const TREND_DOWN = 'down';

    public static function formatAmount(int $amountInCent, string $currencySymbol, int $decimal = 2): string
    {
        return $currencySymbol . number_format($amountInCent / 100, $decimal, '.', ',');
    }

    public static function formatDateFromString(?string $date): ?string
    {
        if (is_null($date)) {
            return null;
        }
        return Carbon::parse($date)->format('Y-m-d');
    }

    public static function formatDateTimeFromString(?string $datetime): ?string
    {
        if (is_null($datetime)) {
            return null;
        }
        return Carbon::parse($datetime)->format('Y-m-d H:i:s');
    }

    public static function formatDate(?Carbon $carbon): ?string
    {
        if (is_null($carbon)) {
            return null;
        }
        return $carbon->format('Y-m-d');
    }

    public static function formatPercentage(int $bookValue, int $marketValue): string
    {
        if ($bookValue === 0) return '0%';

        $rate = ($marketValue / $bookValue - 1);
        $rateInPercentage = number_format($rate * 100, 2, '.', '');
        return $rate >= 0 ? '+' . $rateInPercentage . '%' : $rateInPercentage . '%';
    }

    public static function formatTrend($bookValue, $marketValue): string
    {
        if ($bookValue === 0) return self::TREND_UP;
        $rate = ($marketValue / $bookValue - 1);
        return $rate >= 0 ? self::TREND_UP : self::TREND_DOWN;
    }

}
