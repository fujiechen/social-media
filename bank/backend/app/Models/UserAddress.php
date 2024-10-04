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
 * @property string $address
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $zip
 * @property string $comment
 * @property-read User $user
 */
class UserAddress extends Model
{
    use HasUser;
    use SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'country',
        'province',
        'city',
        'zip',
        'comment',
    ];
}
