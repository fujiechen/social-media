<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $region
 * @property string $bucket
 * @property string $access_key
 * @property string $secret
 * @property string $endpoint_url
 * @property ?string $cdn_url
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class LandingDomain extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';

    protected $fillable = [
        'name',
        'description',
        'access_key',
        'secret',
        'region',
        'bucket',
        'endpoint_url',
        'cdn_url',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];
}
