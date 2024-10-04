<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property ?int $category_id
 * @property string $name
 * @property string $type
 * @property string $country_code
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property ?File $adminPemFile
 * @property ?string $admin_username
 * @property ?string $admin_password
 * @property ?string $admin_url
 * @property ?Category $category
 * @property ?string $api_url
 * @property ?string $api_key
 * @property ?string $api_secret
 * @property ?string $ipsec_shared_key
 * @property ?int $ovpn_file_id
 * @property ?File $ovpnFile
 * @property ?string $ovpn_file_path
 */
class Server extends Model
{
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'country_code',
        'type',
        'ip',
        'admin_url',
        'admin_username',
        'admin_password',
        'admin_pem_file_id',
        'api_url',
        'api_key',
        'api_secret',
        'ipsec_shared_key',
        'ovpn_file_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'admin_pem_file_path',
        'ovpn_file_path',
    ];

    protected $with = [
        'category',
    ];

    const TYPE_IPSEC = 'IPsec';
    const TYPE_OPENVPN = 'Openvpn';

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function adminPemFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'admin_pem_file_id');
    }

    public function getAdminPemFilePathAttribute(): ?string {
        return $this->adminPemFile?->url;
    }

    public function ovpnFile(): BelongsTo {
        return $this->belongsTo(File::class, 'ovpn_file_id');
    }

    public function getOvpnFilePathAttribute(): ?string {
        return $this->ovpnFile?->url;
    }
}
