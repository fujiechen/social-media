<?php

namespace App\Services\PaymentGateway;

use App\Dtos\PaymentGatewayIntent;
use App\Dtos\PaymentGatewayWebhook;
use App\Exceptions\IllegalArgumentException;
use App\Models\Currency;
use App\Models\UserOrderPayment;
use Illuminate\Support\Facades\Http;

class NihaoPaymentGatewayService implements PaymentGatewayServiceInterface
{
    public function createPaymentIntent(PaymentGatewayIntent $paymentGatewayIntent): PaymentGatewayIntent
    {
        try {
            $request = [
                'vendor' => $paymentGatewayIntent->paymentMethod,
                'reference' => $this->createReference($paymentGatewayIntent->userOrderId),
                'ipn_url' => $paymentGatewayIntent->webhookUrl,
                'callback_url' => $paymentGatewayIntent->callbackUrl,
                'response_format' => 'JSON',
                'terminal' => 'WAP',
            ];

            /**
             * https://docs.nihaopay.com/api/v1.2/cn/index.html#%E6%8E%A5%E5%8F%A3%E8%A7%84%E5%88%99
             *
             * 针对商户标价为人民币的订单，可以使用rmb_amount这个参数来替换amount，
             * NihaoPay根据付款渠道方提供的当前汇率将人民币金额转化对应的外币金额，
             * 并在支付成功后返回转换后的外币金额。
             *
             *  `rmb_amount`和`amount`字段是互斥的，两个参数不能同时使用；
             * 使用`rmb_amount`参数时，`currency`表示该笔订单的结算币种。
             */
            if ($paymentGatewayIntent->currencyName == Currency::CNY) {
                $request['currency'] = Currency::CAD;
                $request['rmb_amount'] = (string) $paymentGatewayIntent->amount;
            } else {
                $request['currency'] = $paymentGatewayIntent->currencyName;
                $request['amount'] = (string) $paymentGatewayIntent->amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $paymentGatewayIntent->paymentGatewaySecret,
            ])->asForm()
                ->post($paymentGatewayIntent->endpointUrl, $request);

            if ($response->successful()) {
                $paymentGatewayIntent->paymentIntentId = $response->json('form.params.txnId');
                $paymentGatewayIntent->response = $response->json('form');
                $paymentGatewayIntent->userOrderStatus = UserOrderPayment::STATUS_SUCCESSFUL;
            } else {
                $paymentGatewayIntent->response = $response->json();
                $paymentGatewayIntent->userOrderStatus = UserOrderPayment::STATUS_FAILED;
            }
        } catch (\Exception $e) {
            $paymentGatewayIntent->response = $e->getTrace();
            $paymentGatewayIntent->userOrderStatus = UserOrderPayment::STATUS_FAILED;
        }

        return $paymentGatewayIntent;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function processPaymentWebhook(PaymentGatewayWebhook $paymentGatewayWebhook): PaymentGatewayWebhook
    {
        $params = json_decode($paymentGatewayWebhook->payload, true);

        ksort($params);
        $signStr = "";
        foreach ($params as $key => $val) {
            if($key == 'verify_sign') {
                continue;
            }
            if($val == null || $val == '' || $val == 'null') {
                continue;
            }
            $signStr .= sprintf("%s=%s&", $key, $val);
        }
        $expectSignature = md5($signStr . strtolower(md5($paymentGatewayWebhook->paymentGatewaySecret)));

        if ($paymentGatewayWebhook->signature != $expectSignature) {
            throw new IllegalArgumentException('signature', 'invalid signature');
        }

        if ($params['status'] == 'success') {
            $paymentGatewayWebhook->status = UserOrderPayment::STATUS_SUCCESSFUL;
        } else {
            $paymentGatewayWebhook->status = UserOrderPayment::STATUS_FAILED;
        }
        $paymentGatewayWebhook->paymentIntentId = $params['id'];
        $paymentGatewayWebhook->amount = $params['amount'];
        $paymentGatewayWebhook->response = $params;
        $paymentGatewayWebhook->userOrderId = $this->parseUserOrderIdFromReference($params['reference']);

        return $paymentGatewayWebhook;
    }

    private function createReference(int $userOrderId): string {
        return env('APP_NIHAO_REFERENCE_PREFIX') . env('APP_ENV') . '-' . $userOrderId;
    }

    private function parseUserOrderIdFromReference(string $reference): int {
        $parts = explode('-', $reference);
        return (int) $parts[2];
    }
}
