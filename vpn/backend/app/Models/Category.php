<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $thumbnail_file_id
 * @property File $thumbnailFile
 * @property string $thumbnail_file_path
 * @property array $tags
 * @property array $highlights
 * @property Collection $products
 */
class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'thumbnail_file_id',
        'tags',
        'highlights',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'highlights' => 'array',
    ];

    protected $with = [
        'thumbnailFile'
    ];

    protected $appends = [
        'thumbnail_file_path'
    ];

    public function thumbnailFile(): BelongsTo {
        return $this->belongsTo(File::class, 'thumbnail_file_id');
    }

    public function getThumbnailFilePathAttribute(): string {
        return $this->thumbnailFile->url;
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class);
    }
}
