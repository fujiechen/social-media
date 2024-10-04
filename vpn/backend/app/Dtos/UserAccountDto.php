<?php

namespace App\Dtos;

use App\Utils\DataTransferObject;

class UserAccountDto extends DataTransferObject
{
    public int $userAccountId;
    public string $accountNumber;
    public string $currencyName;
    public string $balance;
}
