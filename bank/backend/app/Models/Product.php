<?php

namespace App\Models;

use App\Models\Traits\HasCurrency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $currency_id
 * @property Currency $currency
 * @property int $product_category_id
 * @property ProductCategory $productCategory
 * @property string $title
 * @property string $name
 * @property int $estimate_rate
 * @property string $description
 * @property int $start_amount
 * @property int $stock
 * @property int $freeze_days
 * @property string $trend
 * @property bool $is_recommend
 * @property string $fund_fact_url
 * @property string $prospectus_url
 * @property string $fund_assets
 * @property ?Carbon $deactivated_at
 * @property-read Collection $productRates
 * @property-read Collection $recent_product_rates
 */
class Product extends Model
{
    use HasCurrency;

    public $timestamps = true;

    public const HISTORY_TREND_DAYS = 365;
    public const HISTORY_TREND_DAYS_VIEWABLE = 10;
    public const TREND_UP = 'up';
    public const TREND_DOWN = 'down';

    protected $attributes = [
        'is_recommend' => false,
    ];

    protected $with = [
        'currency',
        'productCategory',
    ];

    protected $fillable = [
        'title',
        'name',
        'estimate_rate',
        'currency_id',
        'product_category_id',
        'description',
        'start_amount',
        'stock',
        'freeze_days',
        'is_recommend',
        'fund_assets',
        'fund_fact_url',
        'prospectus_url',
        'deactivated_at',
    ];

    protected $casts = [
        'is_recommend' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function productRates(): HasMany
    {
        return $this->hasMany(ProductRate::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function getRecentProductRatesAttribute(): Collection
    {
        return ProductRate::where('product_id', $this->id)
            ->orderBy('id', 'desc')
            ->take(self::HISTORY_TREND_DAYS_VIEWABLE)
            ->get()
            ->reverse();
    }
}
