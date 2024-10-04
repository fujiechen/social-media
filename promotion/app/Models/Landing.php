<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $signature
 * @property int $landing_template_id
 * @property LandingTemplate $landingTemplate
 * @property ?int $post_id
 * @property ?Post $post
 * @property ?int $account_id
 * @property ?Account $account
 * @property string $ip
 * @property string $country
 * @property bool $redirect
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Landing extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'url',
        'signature',
        'landing_template_id',
        'post_id',
        'account_id',
        'ip',
        'country',
        'redirect',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'redirect' => 'boolean'
    ];

    protected $with = [
        'landingTemplate',
        'post',
        'account',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function landingTemplate(): BelongsTo {
        return $this->belongsTo(LandingTemplate::class);
    }

    public function post(): BelongsTo {
        return $this->belongsTo(Post::class);
    }

    public function account(): BelongsTo {
        return $this->belongsTo(Account::class);
    }
}
