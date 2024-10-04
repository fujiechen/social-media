<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $user_order_id
 * @property bool $is_active
 * @property int $total_market_value
 * @property int $total_book_cost
 * @property int $final_market_value
 * @property int $final_book_cost
 * @property-read UserOrder $userOrder
 * @property-read Collection $userProductReturns
 */
class UserProduct extends Model
{
    public $timestamps = true;

    protected $attributes = [
        'is_active' => true,
        'total_market_value' => 0,
        'total_book_cost' => 0,
        'final_market_value' => 0,
        'final_book_cost' => 0,
    ];

    protected $fillable = [
        'user_order_id',
        'is_active',
        'total_market_value',
        'total_book_cost',
        'final_market_value',
        'final_book_cost',
    ];

    public function userOrder(): BelongsTo
    {
        return $this->belongsTo(UserOrder::class);
    }

    public function userProductReturns(): HasMany
    {
        return $this->hasMany(UserProductReturn::class);
    }

}
