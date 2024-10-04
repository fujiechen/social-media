<?php

namespace App\Models;

use App\Events\AccountTypeSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $admin_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AccountType extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'description',
        'contact_type',
        'admin_url',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $dispatchesEvents = [
        'saved' => AccountTypeSavedEvent::class,
    ];
}
