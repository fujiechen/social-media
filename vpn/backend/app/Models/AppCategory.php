<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 *
 */
class AppCategory extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];
}
