<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property ?int $qr_file_id
 * @property ?File $qrFile
 * @property string $fileType
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TargetUrl extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    const STATUS_ACTIVE = 'Active';
    const STATUS_INACTIVE = 'InActive';

    protected $fillable = [
        'name',
        'url',
        'qr_file_id',
        'status',
        'qrFile',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'file_type',
        'created_at_formatted',
        'updated_at_formatted',
    ];

    public function qrFile(): BelongsTo {
        return $this->belongsTo(File::class, 'qr_file_id');
    }

    public function getFileTypeAttribute(): string {
        if ($this->qr_file_id) {
            return 'cloud';
        }

        return 'upload';
    }
}
