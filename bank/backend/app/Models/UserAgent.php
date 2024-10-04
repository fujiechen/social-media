<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property-read User $user
 */
class UserAgent extends Model
{
    use HasUser;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'code',
    ];
}
