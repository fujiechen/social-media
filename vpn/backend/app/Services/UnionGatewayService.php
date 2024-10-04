<?php

namespace App\Services;

use App\Dtos\UserDto;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class UnionGatewayService
{
    private string $baseUrl;

    public function __construct() {
        $this->baseUrl = env('UNION_API_URL');
    }

    public function createUser(UserDto $userDto): Response {
        $api = $this->baseUrl . '/api/auth/register';

        return Http::withHeaders([
            'Accept' => 'application/json'
        ])->post($api, [
            'username' => $userDto->username,
            'email' => $userDto->email,
            'nickname' => $userDto->nickname,
            'password' => $userDto->password,
            'confirm_password' => $userDto->password,
            'language' => $userDto->language,
            'user_share_id' => $userDto->userShareId
        ]);
    }
}
