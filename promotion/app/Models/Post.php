<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $account_id
 * @property Account $account
 * @property string $title
 * @property string $description
 * @property ?string $post_url
 * @property ?array $tags
 * @property string $status
 * @property int $landing_template_id
 * @property LandingTemplate $landingTemplate
 * @property ?int $content_type_id
 * @property ?ContentType $contentType
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Post extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    const STATUS_DRAFT = 'Draft';
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';

    protected $fillable = [
        'instruction',
        'account_id',
        'content_type_id',
        'title',
        'description',
        'post_url',
        'tags',
        'landing_template_id',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    protected $with = [
        'account',
        'contact',
        'landingTemplate',
        'contentType',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function account(): BelongsTo {
        return $this->belongsTo(Account::class);
    }

    public function landingTemplate(): BelongsTo {
        return $this->belongsTo(LandingTemplate::class);
    }

    public function contentType(): BelongsTo {
        return $this->belongsTo(ContentType::class);
    }

}
