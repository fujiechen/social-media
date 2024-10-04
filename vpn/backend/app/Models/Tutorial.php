<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string $os
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property Collection $tutorialFiles
 */
class Tutorial extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'content',
        'os',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $with = [
        'tutorialFiles',
    ];

    const OS_WIN = 'win';
    const OS_MAC = 'mac';
    const OS_IOS = 'ios';
    const OS_ANDROID = 'android';
    const OS_SHARE = 'share';

    public function tutorialFiles(): HasMany {
        return $this->hasMany(TutorialFile::class);
    }
}
