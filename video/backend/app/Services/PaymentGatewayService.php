<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    private string $baseUrl;

    public function __construct() {
        $this->baseUrl = env('WALLET_API_URL');
    }

    public function getUserAccount(string $jwtToken, ?string $currencyName): Response
    {
        $api = $this->baseUrl . '/api/user/accounts';

        $params = '';
        if ($currencyName) {
            $params = '?currency_name=' . $currencyName;
        }

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwtToken,
            'Accept' => 'application/json'
        ])->get($api . $params);
    }

    public function transfer(string $jwtToken, string $fromCurrencyName, float $amount, string $toUserEmail,
                             string $toUserName, ?string $toUserAccessToken, ?string $comment): Response {
        $api = $this->baseUrl . '/api/user/orders/transfer';

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $jwtToken,
            'Accept' => 'application/json'
        ])->post($api, [
            'amount' => $amount,
            'from_currency_name' => $fromCurrencyName,
            'to_user_email' => $toUserEmail,
            'to_user_name' => $toUserName,
            'to_user_access_token' => $toUserAccessToken,
            'comment' => $comment,
        ]);
    }
}
