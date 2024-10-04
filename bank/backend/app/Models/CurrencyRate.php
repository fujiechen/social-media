<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $from_currency_id
 * @property int $to_currency_id
 * @property float $rate
 * @property Currency $fromCurrency
 * @property Currency $toCurrency
 */
class CurrencyRate extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate'
    ];

    protected $with = [
        'fromCurrency',
        'toCurrency',
    ];

    public function fromCurrency(): BelongsTo {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }
    public function toCurrency(): BelongsTo {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

}
