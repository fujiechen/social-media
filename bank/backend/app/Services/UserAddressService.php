<?php

namespace App\Services;

use App\Models\UserAddress;

class UserAddressService
{
    public function upsert(int $userId, ?int $userAddressId, string $name, string $phone, string $address, string $country, string $province, string $city, string $zip, string $comment = null)
    {
        return UserAddress::updateOrCreate(['id' => $userAddressId], [
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'zip' => $zip,
            'comment' => $comment
        ]);
    }

    public function getUserAddressesQuery(int $userId, ?int $userAddressId = null)
    {
        $query = UserAddress::query();
        $query->where('user_id', '=', $userId);

        if (!is_null($userAddressId)) {
            $query->where('id', '=', $userAddressId);
        }

        return $query;
    }
}
