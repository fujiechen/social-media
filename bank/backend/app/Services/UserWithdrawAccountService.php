<?php

namespace App\Services;

use App\Models\UserWithdrawAccount;

class UserWithdrawAccountService
{
    public function upsert(int    $userId, ?int $userWithdrawAccountId, string $name, string $phone, string $accountNumber, string $bankAddress,
                           string $bankName, ?string $comment = null)
    {
        return UserWithdrawAccount::updateOrCreate(['id' => $userWithdrawAccountId], [
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'account_number' => $accountNumber,
            'bank_name' => $bankName,
            'bank_address' => $bankAddress,
            'comment' => $comment,
        ]);
    }

    public function getUserWithdrawAccountsQuery(int $userId, ?int $userWithdrawAccountId = null)
    {
        $query = UserWithdrawAccount::query();
        $query->where('user_id', '=', $userId);

        if (!is_null($userWithdrawAccountId)) {
            $query->where('id', '=', $userWithdrawAccountId);
        }

        return $query;
    }
}
