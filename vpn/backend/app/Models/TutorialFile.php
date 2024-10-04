<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property int $tutorial_id
 * @property int $file_id
 * @property Tutorial $tutorial
 * @property File $file
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 */
class TutorialFile extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'file_id',
        'tutorial_id',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function tutorial(): BelongsTo {
        return $this->belongsTo(Tutorial::class);
    }

    public function file(): BelongsTo {
        return $this->belongsTo(File::class);
    }
}
