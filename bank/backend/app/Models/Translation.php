<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $hash
 * @property string $from_language
 * @property string $from_text
 * @property string $to_language
 * @property string $to_text
 */
class Translation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hash',
        'from_language',
        'from_text',
        'to_language',
        'to_text',
    ];
}
