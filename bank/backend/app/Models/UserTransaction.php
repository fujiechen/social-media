<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use App\Models\Traits\HasUserAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property int $user_account_id
 * @property int $user_order_id
 * @property int $user_product_return_id
 * @property int $amount
 * @property int $balance
 * @property string $status
 * @property string $comment
 * @property User $user
 * @property UserAccount $userAccount
 */
class UserTransaction extends Model
{
    use HasUser;
    use HasUserAccount;
    use HasCreatedAt;
    use HasUpdatedAt;

    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';

    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'type',
        'user_account_id',
        'amount',
        'balance',
        'status',
        'comment',
        'user_order_id',
        'product_id',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'amount_in_dollar',
        'balance_in_dollar'
    ];

    protected $with = [
        'userAccount',
    ];

    public function getAmountInDollarAttribute(): float {
        return $this->amount / 100;
    }

    public function getBalanceInDollarAttribute(): float {
        return $this->balance / 100;
    }

}
