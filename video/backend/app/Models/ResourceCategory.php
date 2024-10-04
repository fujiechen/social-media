<?php

namespace App\Models;

use App\Models\Traits\HasCategory;
use App\Models\Traits\HasResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $resource_id
 * @property ?int $category_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Resource $resource
 * @property ?Category $category
 */
class ResourceCategory extends Model
{
    use HasResource;
    use HasCategory;

    protected $fillable = [
        'name',
        'resource_id',
        'category_id',
        'created_at',
        'updated_at'
    ];
}
