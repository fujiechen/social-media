<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property Collection|Product[] $products
 * @property Collection|Product[] $activeProducts
 */
class ProductCategory extends Model
{
    public $timestamps = false;

    const EQUITY_FUNDS = 'Equity Funds';
    const MULTI_ASSET = 'Multi Asset';
    const FIXED_INCOME = 'Fixed Income';

    protected $fillable = [
        'name',
    ];

    protected $appends = [
        'productsCount',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)
            ->whereNull('products.deactivated_at');
    }

    public function getProductsCountAttribute(): int {
        return $this->activeProducts()->count();
    }
}
