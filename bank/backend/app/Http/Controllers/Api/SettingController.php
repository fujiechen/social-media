<?php
namespace App\Http\Controllers\Api;

use App\Http\Requests\ExchangeUserRequest;
use App\Http\Resources\CurrencyResource;
use App\Http\Resources\SettingResource;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserOrder;
use App\Models\UserTransaction;
use App\Services\CurrencyRateService;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingController extends BaseController
{
    private CurrencyRateService $currencyRateService;
    private TranslationService $translationService;

    public function __construct(CurrencyRateService $currencyRateService, TranslationService $translationService) {
        $this->currencyRateService = $currencyRateService;
        $this->translationService = $translationService;
    }

    public function index(Request $request) {
        $names = $request->get('names');
        $query = Setting::query();
        if (!is_null($names)) {
            $namesArray = explode(',', $names);
            $query->whereIn('name', $namesArray);
        }
        $settings = $query->get();

        $decoratedSettings = [];
        foreach($settings as $setting) {
            $decoratedSettings[] = $this->translationService->translateSetting($setting, $this->getLanguage($request));
        }
        $settings = $decoratedSettings;

        return SettingResource::collection($settings);
    }

    public function show(Request $request, $name) {
        $setting = Setting::where('name', '=', $name)->first();
        $this->translationService->translateSetting($setting, $this->getLanguage($request));
        return new SettingResource($setting);
    }

    public function currencyRate(ExchangeUserRequest $request) {
        $fromCurrencyId = $request->get('from_currency_id');
        $toCurrencyId = $request->get('to_currency_id');
        $fromAmount = $request->get('amount');
        $rate = $this->currencyRateService->getExchangeRate($fromCurrencyId, $toCurrencyId);
        $toAmount = $this->currencyRateService->exchange($fromCurrencyId, $toCurrencyId, $fromAmount);

        return new JsonResource([
            'fromCurrency' => new CurrencyResource(Currency::find($fromCurrencyId)),
            'toCurrency' => new CurrencyResource(Currency::find($toCurrencyId)),
            'rate' => $rate,
            'fromAmount' => $fromAmount,
            'toAmount' => $toAmount,
        ]);
    }

    public function userOrderType() {
        return JsonResource::collection([[
            UserOrder::TYPE_DEPOSIT => UserOrder::TYPE_DEPOSIT,
            UserOrder::TYPE_PURCHASE => UserOrder::TYPE_PURCHASE,
            UserOrder::TYPE_WITHDRAW => UserOrder::TYPE_WITHDRAW,
            UserOrder::TYPE_TRANSFER => UserOrder::TYPE_TRANSFER,
        ]]);
    }

    public function userOrderStatus() {
        return JsonResource::collection([[
            UserOrder::STATUS_PENDING => UserOrder::STATUS_PENDING,
            UserOrder::STATUS_SUCCESSFUL => UserOrder::STATUS_SUCCESSFUL,
            UserOrder::STATUS_FAILED => UserOrder::STATUS_FAILED,
        ]]);
    }

    public function userTransactionType() {
        return JsonResource::collection([[
            UserTransaction::TYPE_INCOME => UserTransaction::TYPE_INCOME,
            UserTransaction::TYPE_EXPENSE => UserTransaction::TYPE_EXPENSE,
        ]]);
    }

    public function userTransactionStatus() {
        return JsonResource::collection([[
            UserTransaction::STATUS_SUCCESSFUL => UserTransaction::STATUS_SUCCESSFUL,
            UserTransaction::STATUS_FAILED => UserTransaction::STATUS_FAILED,
        ]]);
    }

}
