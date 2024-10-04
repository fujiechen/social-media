<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 */
class Meta extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'key',
        'value',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    const BANNER_HOME_URL = 'BANNER_HOME_URL';
    const SHARE_TEXT = 'SHARE_TEXT';
    const SHARE_IMAGE_URL = 'SHARE_IMAGE_URL';
    const SHARE_INSTRUCTION_HTML = 'SHARE_INSTRUCTION_HTML';
    const CUSTOMER_SERVICE_QR_HTML = 'CUSTOMER_SERVICE_QR_HTML';

    const META_KEYS = [
        self::BANNER_HOME_URL,
        self::SHARE_TEXT,
        self::SHARE_IMAGE_URL,
        self::SHARE_INSTRUCTION_HTML,
        self::CUSTOMER_SERVICE_QR_HTML
    ];
}
