<?php

namespace App\Models;

use App\Models\Traits\HasResource;
use App\Models\Traits\HasTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $resource_id
 * @property ?int $tag_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Resource $resource
 * @property ?Tag $tag
 */
class ResourceTag extends Model
{
    use HasResource;
    use HasTag;

    protected $fillable = [
        'name',
        'resource_id',
        'tag_id',
        'created_at',
        'updated_at'
    ];
}
