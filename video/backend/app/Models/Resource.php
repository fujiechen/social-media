<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Resource extends Model
{
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];
}
