<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $access_token
 * @property string $notifier_url
 * @property int $max_retry_times
 */
class OrderNotifier extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'access_token',
        'notifier_url',
        'max_retry_times',
    ];
}
