<?php

namespace App\Models;

use App\Events\ContactSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $contact
 * @property string $type
 * @property string $description
 * @property string $admin_url
 * @property string $admin_username
 * @property string $admin_password
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Contact extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'contact',
        'type',
        'description',
        'admin_url',
        'admin_username',
        'admin_password',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];


    protected $dispatchesEvents = [
        'saved' => ContactSavedEvent::class
    ];

    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';


}
