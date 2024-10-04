<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $url
 * @property int $icon_file_id
 * @property File $iconFile
 * @property string $icon_file_path
 * @property AppCategory $appCategory
 * @property int $app_category_id
 * @property bool $is_hot
 */
class App extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'description',
        'url',
        'icon_file_id',
        'app_category_id',
        'is_hot',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_hot' => 'boolean',
    ];

    protected $with = [
        'iconFile',
        'appCategory'
    ];

    protected $appends = [
        'icon_file_path',
        'is_hot_int',
        'created_at_formatted',
        'updated_at_formatted',
    ];


    public function getIsHotIntAttribute(): int {
        return $this->is_hot ? 1 : 0;
    }

    public function iconFile(): BelongsTo {
        return $this->belongsTo(File::class, 'icon_file_id');
    }

    public function getIconFilePathAttribute(): string {
        return $this->iconFile->url;
    }

    public function appCategory(): BelongsTo {
        return $this->belongsTo(AppCategory::class);
    }
}
