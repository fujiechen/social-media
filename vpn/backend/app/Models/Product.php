<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $thumbnail_file_id
 * @property int $unit_cents
 * @property string $frequency
 * @property Carbon $updated_at
 * @property Carbon $created_at
 * @property Collection $images
 * @property File $thumbnailFile
 * @property float $unit_price
 * @property string $currency_name
 * @property int $frequency_as_extend_days
 * @property string $thumbnail_file_path
 * @property array $product_image_paths
 * @property Category $category
 * @property int $category_id
 * @property ?int $order_num_allowance
 */
class Product extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;
    use SoftDeletes;

    protected $fillable = [
        'order_num_allowance',
        'name',
        'description',
        'category_id',
        'thumbnail_file_id',
        'currency_name',
        'unit_cents',
        'frequency',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $with = [
        'thumbnailFile',
        'category',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'unit_price',
        'thumbnail_file_path',
        'product_image_paths',
        'frequency_as_extend_days'
    ];

    const WEEKLY = 'weekly';
    const MONTHLY = 'monthly';
    const QUARTERLY = 'quarterly';
    const YEARLY = 'yearly';

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function getThumbnailFilePathAttribute(): string {
        return $this->thumbnailFile->url;
    }

    public function getProductImagePathsAttribute(): array {
        $paths = [];

        foreach ($this->images as $image) {
            $paths[] = $image->url;
        }

        return $paths;
    }

    public function images(): HasManyThrough
    {
        return $this->hasManyThrough(File::class, ProductImage::class,
            'product_id', 'id', 'id', 'file_id');
    }

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function getUnitPriceAttribute(): float {
        return $this->unit_cents / 100;
    }

    public function getFrequencyAsExtendDaysAttribute(): ?int {
        if ($this->frequency == self::WEEKLY) {
            return 7;
        }

        if ($this->frequency == self::MONTHLY) {
            return 30;
        }

        if ($this->frequency == self::QUARTERLY) {
            return 90;
        }

        if ($this->frequency == self::YEARLY) {
            return 365;
        }

        return null;
    }

}

