<?php

namespace App\Services;

use App\Models\AccountType;
use Illuminate\Support\Collection;

class AccountTypeService
{
    public function fetchAllAccountTypes(string $contactType): Collection {
        return AccountType::query()->where('contact_type', '=', $contactType)->get();
    }
}
