<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Server $server
 * @property int $server_id
 * @property User $user
 * @property int $user_id
 * @property Carbon $valid_until_at
 * @property ?string $radius_username
 * @property ?string $radius_password
 * @property ?string $radius_uuid
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 */
class ServerUser extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;
    use HasUser;

    protected $fillable = [
        'user_id',
        'server_id',
        'radius_uuid',
        'radius_username',
        'radius_password',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $casts = [
        'created' => 'datetime',
        'updated' => 'datetime',
    ];

    protected $with = [
        'user',
        'server',
    ];

    public function server(): BelongsTo {
        return $this->belongsTo(Server::class);
    }
}
