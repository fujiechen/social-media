<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $queue
 * @property array $payload
 * @property int $attempts
 *
 */
class Job extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'queue',
        'payload',
        'attempts',
        'created_at',
        'reserved_at',
        'available_at'
    ];

    protected $casts = [
        'payload' => 'array',
    ];

}
