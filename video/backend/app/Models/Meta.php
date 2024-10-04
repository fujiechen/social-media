<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $meta_key
 * @property string $meta_value
 */
class Meta extends Model
{
    use HasCreatedAt;

    protected $fillable = [
        'meta_key',
        'meta_value',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'created_at_formatted'
    ];

    const POINTS_TO_CENTS = 'POINTS_TO_CENTS';
    const VISITOR_REFERRAL_POINTS = 'VISITOR_REFERRAL_POINTS';
    const USER_REFERRAL_POINTS = 'USER_REFERRAL_POINTS';
    const MEMBERSHIP_REFERRAL_POINTS = 'MEMBERSHIP_REFERRAL_POINTS';
    const PRODUCTS_BANNER_URL = 'PRODUCTS_BANNER_URL';
    const SHARE_IMAGE_URL = 'SHARE_IMAGE_URL';
    const SHARE_TEXT = 'SHARE_TEXT';
    const CUSTOMER_SERVICE_QR_HTML = 'CUSTOMER_SERVICE_QR_HTML';
    const NEW_USER_POINTS = 'NEW_USER_POINTS';
    const PRODUCT_OWNER_PERCENTAGE = 'PRODUCT_OWNER_PERCENTAGE';
    const REGISTRATION_HTML = 'REGISTRATION_HTML';

    const MEMBERSHIP_REFERRAL_HTML = 'MEMBERSHIP_REFERRAL_HTML';
    const EARN_POINTS_HTML = 'EARN_POINTS_HTML';


    const META_KEYS = [
        self::POINTS_TO_CENTS,
        self::VISITOR_REFERRAL_POINTS,
        self::USER_REFERRAL_POINTS,
        self::MEMBERSHIP_REFERRAL_POINTS,
        self::MEMBERSHIP_REFERRAL_HTML,
        self::PRODUCTS_BANNER_URL,
        self::SHARE_IMAGE_URL,
        self::SHARE_TEXT,
        self::CUSTOMER_SERVICE_QR_HTML,
        self::NEW_USER_POINTS,
        self::PRODUCT_OWNER_PERCENTAGE,
        self::REGISTRATION_HTML,
        self::EARN_POINTS_HTML
    ];
}
