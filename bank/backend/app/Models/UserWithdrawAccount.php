<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $phone
 * @property string $account_number
 * @property string $bank_name
 * @property string $bank_address
 * @property string $comment
 * @property User $user
 */
class UserWithdrawAccount extends Model
{
    use HasUser;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'account_number',
        'bank_name',
        'bank_address',
        'comment',
    ];
}
