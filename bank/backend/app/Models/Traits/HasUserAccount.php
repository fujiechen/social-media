<?php

namespace App\Models\Traits;

use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUserAccount
{
    public function userAccount(): BelongsTo
    {
        return $this->belongsTo(UserAccount::class);
    }
}
