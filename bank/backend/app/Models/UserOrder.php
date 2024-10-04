<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUserAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $type
 * @property int $user_account_id
 * @property int $product_id
 * @property int $to_user_account_id
 * @property int $to_user_withdraw_account_id
 * @property int $to_user_address_id
 * @property int $amount
 * @property int $start_amount
 * @property int $freeze_days
 * @property string $status
 * @property string $comment
 * @property Carbon $created_at
 * @property-read Product $product
 * @property-read UserAccount $userAccount
 * @property-read UserAccount $toUserAccount
 * @property-read UserWithdrawAccount $toUserWithdrawAccount
 * @property-read UserTransaction $userTransactions
 * @property array $meta_json
 */
class UserOrder extends Model
{
    use HasUserAccount;
    use HasCreatedAt;
    use HasUpdatedAt;

    public $timestamps = true;

    public const TYPES = [
        self::TYPE_DEPOSIT,
        self::TYPE_PURCHASE,
        self::TYPE_WITHDRAW,
        self::TYPE_EXCHANGE,
        self::TYPE_TRANSFER,
    ];

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_PURCHASE = 'purchase';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_EXCHANGE = 'exchange';
    public const TYPE_TRANSFER = 'transfer';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';

    public const WITHDRAW_WIRED_MIN_USD = 1000;
    public const WITHDRAW_WIRED_PROCESSING_FEE_RATE = 0.05;
    public const WITHDRAW_CASH_MIN_USD = 100000;
    public const WITHDRAW_CASH_PROCESSING_FEE_RATE = 0.2;


    protected $fillable = [
        'type',
        'user_account_id',
        'product_id',
        'to_user_account_id',
        'to_user_withdraw_account_id',
        'to_user_address_id',
        'amount',
        'start_amount',
        'freeze_days',
        'status',
        'comment',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    protected $with = [
        'userAccount',
        'product',
        'toUserAccount',
        'toUserWithdrawAccount',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'amountInDollar',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function toUserAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class, 'to_user_account_id');
    }

    public function toUserWithdrawAccount(): BelongsTo
    {
        return $this->belongsTo(UserWithdrawAccount::class, 'to_user_withdraw_account_id');
    }

    public function userTransactions(): HasMany
    {
        return $this->hasMany(UserTransaction::class);
    }

    public function userOrderPayments(): HasMany
    {
        return $this->hasMany(UserOrderPayment::class);
    }

    public function getReleaseDate(): ?Carbon
    {
        if ($this->type === self::TYPE_PURCHASE) {
            return Carbon::parse($this->created_at)->addDays($this->freeze_days);
        }
        return null;
    }

    public function getAmountInDollarAttribute(): float {
        return $this->amount / 100;
    }

}
