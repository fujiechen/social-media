<?php

namespace App\Models;

use App\Events\LandingTemplateSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $landing_html
 * @property int $redirect_type_id
 * @property RedirectType $redirectType
 * @property int $banner_file_id
 * @property File $bannerFile
 * @property int $target_url_id
 * @property TargetUrl $targetUrl
 * @property string $landing_url
 * @property string $status
 * @property LandingDomain $landingDomain
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LandingTemplate extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'description',
        'landing_html',
        'redirect_type_id',
        'banner_file_id',
        'target_url_id',
        'landing_domain_id',
        'status',
        'landing_url',
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'redirectType',
        'targetUrl',
        'landingDomain',
        'bannerFile',
    ];

    protected $appends = [
        'file_type',
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $dispatchesEvents = [
        'saved' => LandingTemplateSavedEvent::class
    ];

    const STATUS_DRAFT = 'Draft';
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';

    public function redirectType(): BelongsTo {
        return $this->belongsTo(RedirectType::class);
    }

    public function bannerFile(): BelongsTo {
        return $this->belongsTo(File::class, 'banner_file_id');
    }

    public function targetUrl(): BelongsTo {
        return $this->belongsTo(TargetUrl::class);
    }

    public function landingDomain(): BelongsTo {
        return $this->belongsTo(LandingDomain::class);
    }

    public function getFileTypeAttribute(): string {
        if ($this->banner_file_id) {
            return 'cloud';
        }

        return 'upload';
    }
}
