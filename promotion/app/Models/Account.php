<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $instruction
 * @property int $contact_id
 * @property int $account_type_id
 * @property ?string $account_no
 * @property ?string $account_url
 * @property ?string $admin_username
 * @property ?string admin_password
 * @property ?int $profile_avatar_file_id
 * @property ?FIle $profileAvatarFile
 * @property int $landing_template_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Account extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    const STATUS_DRAFT = 'Draft';
    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';

    protected $fillable = [
        'instruction',
        'contact_id',
        'account_type_id',
        'nickname',
        'account_no',
        'account_url',
        'admin_username',
        'admin_password',
        'profile_description',
        'profile_avatar_file_id',
        'landing_template_id',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $with = [
        'contact',
        'accountType',
        'landingTemplate',
        'profileAvatarFile'
    ];

    protected $appends = [
        'file_type',
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function accountType(): BelongsTo {
        return $this->belongsTo(AccountType::class);
    }

    public function profileAvatarFile(): BelongsTo {
        return $this->belongsTo(File::class, 'profile_avatar_file_id');
    }

    public function landingTemplate(): BelongsTo {
        return $this->belongsTo(LandingTemplate::class);
    }

    public function getFileTypeAttribute(): string {
        if ($this->profile_avatar_file_id) {
            return 'cloud';
        }

        return 'upload';
    }
}
