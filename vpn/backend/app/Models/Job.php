<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
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
    use HasCreatedAt;

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
        'created_at' => 'datetime',
        'payload' => 'array',
    ];

    protected $appends = [
        'created_at_formatted',
    ];

}
