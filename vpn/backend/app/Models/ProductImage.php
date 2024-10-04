<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property File $file
 */
class ProductImage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'file_id',
    ];

    public function file(): BelongsTo {
        return $this->belongsTo(File::class);
    }
}

