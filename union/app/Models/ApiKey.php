<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $key
 */
class ApiKey extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'id',
        'name',
        'key',
    ];
}
