<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $comment
 * @property-read User $user
 */
class UserSupport extends Model
{
    use HasUser;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'comment',
    ];
}
