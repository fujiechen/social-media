<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

trait HasFormatter
{
    public function formatAmount(int $amountInCent, int $decimal = 2): string
    {
        return number_format($amountInCent / 100, $decimal, '.', ',');
    }

    public function formatBasisPointToPercentage(int $basisPoint, int $decimal = 2): string
    {
        return number_format($basisPoint / 100, $decimal, '.', ',');
    }

    public function formatDate(Carbon $carbon): string
    {
        return $carbon->format('Y-m-d');
    }

    public function formatDateTime(Carbon $carbon): string
    {
        return $carbon->format('Y-m-d H:i:s');
    }
}
