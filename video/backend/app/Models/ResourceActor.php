<?php

namespace App\Models;

use App\Models\Traits\HasActor;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $country
 * @property int $resource_id
 * @property ?int $actor_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Resource $resource
 * @property ?Actor $actor
 */
class ResourceActor extends Model
{
    use HasResource;
    use HasActor;

    protected $fillable = [
        'name',
        'country',
        'resource_id',
        'actor_id',
        'created_at',
        'updated_at'
    ];
}
