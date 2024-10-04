<?php

namespace App\Events;

use App\Models\AccountType;
use Illuminate\Queue\SerializesModels;

class AccountTypeSavedEvent
{
    use SerializesModels;

    public AccountType $accountType;

    public function __construct(AccountType $accountType) {
        $this->accountType = $accountType;
    }
}
