<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_product_id
 * @property int $product_rate_id
 * @property int $market_value
 * @property int $book_cost
 * @property string $comment
 * @property-read UserProduct $userProduct
 * @property-read ProductRate $productRate
 */
class UserProductReturn extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'user_product_id',
        'product_rate_id',
        'market_value',
        'book_cost',
        'comment',
    ];

    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }

    public function productRate(): BelongsTo
    {
        return $this->belongsTo(ProductRate::class);
    }
}
