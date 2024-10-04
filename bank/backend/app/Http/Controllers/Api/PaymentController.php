<?php
namespace App\Http\Controllers\Api;

use App\Exceptions\IllegalArgumentException;
use App\Models\PaymentGateway;
use App\Services\UserOrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends BaseController
{
    private UserOrderPaymentService $userOrderPaymentService;

    public function __construct(UserOrderPaymentService $userOrderPaymentService) {
        $this->userOrderPaymentService = $userOrderPaymentService;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function webhook(Request $request, int $paymentGatewayId) {

        Log::info('received payment gateway webhook calls of payment gateway: ' . $paymentGatewayId);

        /**
         * @var PaymentGateway $paymentGateway
         */
        $paymentGateway = PaymentGateway::findOrFail($paymentGatewayId);

        if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_STRIPE) {
            $payload = @file_get_contents('php://input');
            Log::info($payload);
            if (!isset($_SERVER['HTTP_STRIPE_SIGNATURE'])) {
                throw new IllegalArgumentException();
            }
            $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            Log::info('sig=' . $signature);
            $this->userOrderPaymentService->handleWebhookEvent($paymentGatewayId, $payload, $signature);
        } else if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_NIHAO) {
            Log::info(json_encode($request->all()));
            $this->userOrderPaymentService
                ->handleWebhookEvent($paymentGatewayId, json_encode($request->all()),
                    $request->input('verify_sign'));
            /**
             * https://docs.nihaopay.com/api/v1.2/cn/index.html#%E5%BC%82%E6%AD%A5%E9%80%9A%E7%9F%A5
             * 必须保证异步通知页面(ipn_url)上无任何字符，如空格，HTML标签等，程序执行完成后必须打印输入“ok”(不包括引号)，
             * 如果商户反馈给NihaoPay的字符不是ok这2个字符，NihaoPay服务器会不断重发通知，最多发送8次
             * 一般情况下，NihaoPay会在支付成功后的2小时内完成8次通知（通知的间隔频率为：0m,1m,2m,4m,8m,20m,30m）
             */
            echo "ok";
        }
    }
}
