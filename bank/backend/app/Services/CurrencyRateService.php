<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\CurrencyRate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CurrencyRateService
{
    const USERNAME = 'shopifyplugincompany298850959';
    const PASSWORD = 'ohti97sth8i28ih1np5af29uq2';

    public function getExchangeRate(int $fromCurrencyId, int $toCurrencyId): float
    {
        $fromCurrency = Currency::find($fromCurrencyId);
        if ($fromCurrency->name == Currency::USDT) {
            $fromCurrency = Currency::where('name', Currency::USD)->first();
        }

        $toCurrency = Currency::find($toCurrencyId);
        if ($toCurrency->name == Currency::USDT) {
            $toCurrency = Currency::where('name', Currency::USD)->first();
        }

        if ($fromCurrency->id == $toCurrency->id) {
            return 1;
        }

        $currencyRate = CurrencyRate::query()->where('from_currency_id', '=', $fromCurrencyId)
            ->where('to_currency_id', '=', $toCurrencyId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$currencyRate) { //TODO change another interface to get exchange rate
            $url = 'https://xecdapi.xe.com/v1/convert_from.json/?from=' . $fromCurrency->name . '&to=' . $toCurrency->name . '&amount=1';
            $response = Http::withBasicAuth(self::USERNAME, self::PASSWORD)->get($url);
            $rate = \json_decode($response->body(), true)['to'][0]['mid'];
            CurrencyRate::updateOrCreate(
                [
                    'from_currency_id' => $fromCurrencyId,
                    'to_currency_id' => $toCurrencyId
                ],
                [
                    'rate' => $rate,
                ]);
        } else {
            $rate = $currencyRate->rate;
        }

        return $rate;
    }

    public function createExchangeRates(int $fromCurrencyId, array $toCurrencyIds): void
    {
        $fromCurrency = Currency::find($fromCurrencyId);
        if ($fromCurrency->name == Currency::USDT) {
            $fromCurrency = Currency::where('name', Currency::USD)->first();
        }

        $toCurrencyNames = [];

        foreach ($toCurrencyIds as $toCurrencyId) {
            $toCurrency = Currency::find($toCurrencyId);
            if ($toCurrency->name == Currency::USDT) {
                $toCurrency = Currency::where('name', Currency::USD)->first();
            }

            if ($fromCurrency->id == $toCurrency->id) {
                continue;
            }
            $toCurrencyNames[] = $toCurrency->name;
        }

        $url = 'https://xecdapi.xe.com/v1/convert_from.json/?from=' . $fromCurrency->name
            . '&to=' . implode(',', $toCurrencyNames) . '&amount=1';

        $response = Http::withBasicAuth(self::USERNAME, self::PASSWORD)->get($url);
        $rateResponses = \json_decode($response->body(), true)['to'];
        foreach ($rateResponses as $rateResponse) {
            $toCurrencyName = $rateResponse['quotecurrency'];
            $toCurrency = Currency::where('name', $toCurrencyName)->first();
            CurrencyRate::create([
                'from_currency_id' => $fromCurrencyId,
                'to_currency_id' => $toCurrency->id,
                'rate' => $rateResponse['mid'],
            ]);
        }
    }


    public function exchange(int $fromCurrencyId, int $toCurrencyId, $amount): float
    {
        $rate = $this->getExchangeRate($fromCurrencyId, $toCurrencyId);
        return $amount * $rate;
    }
}
