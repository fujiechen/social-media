<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $symbol
 * @property boolean $is_default
 * @property boolean $purchase_enabled
 * @property boolean $deposit_enabled
 * @property boolean $withdraw_enabled
 * @property boolean $exchange_enabled
 * @property boolean $transfer_enabled
 */
class Currency extends Model
{
    public const CNY = 'CNY';
    public const COIN = 'COIN';
    public const USDT = 'USDT';
    public const USD = 'USD';
    public const GBP = 'GBP';
    public const EUR = 'EUR';
    public const MXN = 'MXN';
    public const BRL = 'BRL';
    public const AUD = 'AUD';
    public const CAD = 'CAD';
    public const JPY = 'JPY';
    public const HKD = 'HKD';
    public const KRW = 'KRW';
    public const PHP = 'PHP';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'symbol',
        'is_default',
        'purchase_enabled',
        'deposit_enabled',
        'withdraw_enabled',
        'exchange_enabled',
        'transfer_enabled',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'purchase_enabled' => 'boolean',
        'deposit_enabled' => 'boolean',
        'withdraw_enabled' => 'boolean',
        'exchange_enabled' => 'boolean',
        'transfer_enabled' => 'boolean',
    ];
}
