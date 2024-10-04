<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Collection $contentTypeFiles
 * @property Collection $files
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ContentType extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'description',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'file_ids',
        'file_paths',
        'file_type',
        'file_urls',
    ];

    /**
     * @return HasMany
     */
    public function contentTypeFiles(): HasMany
    {
        return $this->hasMany(ContentTypeFile::class);
    }

    public function files(): HasManyThrough
    {
        return $this->hasManyThrough(File::class, ContentTypeFile::class,
            'content_type_id', 'id', 'id', 'file_id');
    }

    public function getFileIdsAttribute(): string {
        return $this->files->pluck('id');
    }

    public function getFilePathsAttribute(): string {
        return $this->files->pluck('upload_path');
    }

    public function getFileUrlsAttribute(): array {
        $urls = [];
        foreach ($this->files as $file) {
            $urls[] = $file->url;
        }
        return $urls;
    }

    public function getFileTypeAttribute(): string {
        if ($this->files->count() > 0) {
            return 'cloud';
        }

        return 'upload';
    }
}
