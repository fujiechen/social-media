<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $secret
 * @property string $created_at_formatted
 * @property string $updated_at_formatted
 * @property string $payment_gateway_type
 * @property array $payment_methods
 * @property ?string $webhook_url
 * @property ?string $endpoint_url
 * @property ?string $webhook_secret
 * @property boolean $is_active
 */
class PaymentGateway extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    public $timestamps = true;

    protected $fillable = [
        'payment_gateway_type',
        'name',
        'public',
        'secret',
        'webhook_secret',
        'payment_methods',
        'webhook_url',
        'endpoint_url',
        'is_active',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'is_active' => 'boolean',
    ];

    const TYPE_STRIPE = 'stripe';
    const TYPE_NIHAO = 'nihao';

    const METHOD_CC = 'credit_card';
    const METHOD_ALIPAY = 'alipay';
    const METHOD_WECHAT = 'wechatpay';
    const METHOD_UNION = 'unionpay';

}
