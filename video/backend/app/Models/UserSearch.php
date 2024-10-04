<?php

namespace App\Models;

use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property User $user
 * @property string $search
 */
class UserSearch extends Model
{
    use HasUser;

    protected $table = 'user_searches';

    protected $fillable = [
        'user_id',
        'search',
        'created_at',
        'updated_at',
    ];

    protected $with = [
        'user',
    ];
}
