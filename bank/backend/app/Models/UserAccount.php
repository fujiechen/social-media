<?php

namespace App\Models;

use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasCurrency;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $user_id
 * @property string $account_number
 * @property int $currency_id
 * @property Currency $currency
 * @property int $product_balance
 * @property int $balance
 * @property-read User $user
 */
class UserAccount extends Model
{
    use HasUser;
    use HasCurrency;
    use HasCreatedAt;
    use HasUpdatedAt;

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'currency_id',
        'account_number',
        'balance',
        'product_balance',
    ];

    protected $with = [
        'user',
        'currency'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'balance_in_dollar'
    ];

    public function getAssets(): int
    {
        return $this->balance + $this->product_balance;
    }

    public function getBalanceInDollarAttribute(): float
    {
        return $this->balance / 100;
    }
}
