<?php

namespace App\Services;

use App\Dtos\PaymentGatewayIntent;
use App\Dtos\PaymentGatewayWebhook;
use App\Exceptions\IllegalArgumentException;
use App\Models\PaymentGateway;
use App\Models\UserOrder;
use App\Models\UserOrderPayment;
use App\Services\PaymentGateway\NihaoPaymentGatewayService;
use App\Services\PaymentGateway\StripePaymentGatewayService;
use Illuminate\Support\Facades\Log;

class UserOrderPaymentService extends BaseUserService
{
    /**
     * @throws IllegalArgumentException
     */
    public function createUserOrderPayment(int $userOrderId, string $paymentMethod, ?string $callBackUrl): UserOrderPayment
    {
        /**
         * @var UserOrder $userOrder
         */
        $userOrder = UserOrder::find($userOrderId);

        Log::info('createUserOrderPayment for user order id: ' . $userOrderId);
        /**
         * Find a random payment gateway
         *
         * @var PaymentGateway $paymentGateway ;
         */
        $paymentGateway = PaymentGateway::query()
            ->whereJsonContains('payment_methods', $paymentMethod)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();
        Log::info('createUserOrderPayment found payment gateway id: ' . $paymentGateway->id);

        if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_STRIPE) {
            $paymentGatewayService = new StripePaymentGatewayService();
            $paymentIntentDto = $paymentGatewayService->createPaymentIntent(new PaymentGatewayIntent([
                'userOrderId' => $userOrderId,
                'paymentMethod' => $paymentMethod,
                'amount' => $userOrder->amount,
                'currencyName' => $userOrder->userAccount->currency->name,
                'paymentGatewaySecret' => $paymentGateway->secret,
            ]));
        } else if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_NIHAO) {
            $paymentGatewayService = new NihaoPaymentGatewayService();
            $paymentIntentDto = $paymentGatewayService->createPaymentIntent(new PaymentGatewayIntent([
                'userOrderId' => $userOrderId,
                'endpointUrl' => $paymentGateway->endpoint_url,
                'paymentMethod' => $paymentMethod,
                'amount' => $userOrder->amount,
                'currencyName' => $userOrder->userAccount->currency->name,
                'paymentGatewaySecret' => $paymentGateway->secret,
                'callbackUrl' => $callBackUrl,
                'webhookUrl' => $paymentGateway->webhook_url,
            ]));
        } else {
            throw new IllegalArgumentException('payment gateway is not defined');
        }
        Log::info('createUserOrderPayment payment gateway intent created for user order id: ' . $userOrderId);

        $request = $paymentIntentDto->toArray();
        unset($request['response']);
        return UserOrderPayment::create([
            'user_order_id' => $userOrderId,
            'payment_gateway_id' => $paymentGateway->id,
            'amount' => $userOrder->amount,
            'action' => UserOrderPayment::ACTION_CREATE,
            'stripe_intent_id' => $paymentIntentDto->paymentIntentId ?? null,
            'stripe_intent_client_secret' => $paymentIntentDto->clientSecret ?? null,
            'status' => $paymentIntentDto->userOrderStatus,
            'request' => json_encode($request),
            'response' => $paymentIntentDto?->response,
        ]);
    }


//    /**
//     * Retrieve pending orders only
//     *
//     * @param int $userOrderId
//     * @return UserOrderPayment
//     */
//    public function retrieveUserOrderPayment(int $userOrderId): UserOrderPayment
//    {
//        /**
//         * @var UserOrderPayment $userOrderPayment
//         */
//        $userOrderPayment = UserOrderPayment::query()
//            ->join('user_orders', 'user_order_payments.user_order_id', '=', 'user_orders.id')
//            ->where('user_order_id', '=', $userOrderId)
//            ->where('user_order_payments.status', '=', UserOrderPayment::STATUS_SUCCESSFUL)
//            ->where('user_orders.status', '=', UserOrder::STATUS_PENDING)
//            ->where('action', '=', UserOrderPayment::ACTION_CREATE)
//            ->first();
//
//        $paymentGateway = $userOrderPayment->paymentGateway;
//        $stripeIntentId = $userOrderPayment->stripe_intent_id;
//
//        try {
//            $stripe = new StripeClient($paymentGateway->secret);
//            $paymentIntent = $stripe->paymentIntents->retrieve($stripeIntentId);
//            $response = $paymentIntent->jsonSerialize();
//            $amount = $response['amount'];
//            $status = $response['status'] == 'succeed' ? UserOrderPayment::STATUS_SUCCESSFUL : UserOrderPayment::STATUS_FAILED;
//            $stripeIntentClientSecret = $response['client_secret'];
//        } catch (\Exception $e) {
//            $response = $e->getTrace();
//            $status = UserOrderPayment::STATUS_FAILED;
//            $amount = 0;
//            $stripeIntentClientSecret = null;
//        }
//
//        return UserOrderPayment::create([
//            'user_order_id' => $userOrderId,
//            'payment_gateway_id' => $paymentGateway->id,
//            'amount' => $amount,
//            'action' => UserOrderPayment::ACTION_RETRIEVE,
//            'stripe_intent_id' => $stripeIntentId,
//            'stripe_intent_client_secret' => $stripeIntentClientSecret,
//            'status' => $status,
//            'request' => null,
//            'response' => $response,
//        ]);
//    }

    /**
     * @throws IllegalArgumentException
     */
    public function handleWebhookEvent(int $paymentGatewayId, string $payload, string $signature): UserOrderPayment
    {
        /**
         * @var PaymentGateway $paymentGateway
         */
        $paymentGateway = PaymentGateway::find($paymentGatewayId);

        if (empty($paymentGateway)) {
            throw new IllegalArgumentException("payment_gateway_id", "payment gateway not found");
        }

        try {
            if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_STRIPE) {
                $paymentGatewayService = new StripePaymentGatewayService();
                $paymentGatewayWebhook = $paymentGatewayService
                    ->processPaymentWebhook(new PaymentGatewayWebhook([
                        'payload' => $payload,
                        'signature' => $signature,
                        'webhookSecret' => $paymentGateway->webhook_secret,
                    ]));
                /**
                 * @var UserOrderPayment $userOrderPayment
                 */
                $userOrderPayment = UserOrderPayment::query()
                    ->where('stripe_intent_id', '=', $paymentGatewayWebhook->paymentIntentId)
                    ->first();
                if ($userOrderPayment) {
                    $userOrderId = $userOrderPayment->user_order_id;
                }
            }
            else if ($paymentGateway->payment_gateway_type == PaymentGateway::TYPE_NIHAO) {
                $paymentGatewayService = new NihaoPaymentGatewayService();
                $paymentGatewayWebhook = $paymentGatewayService
                    ->processPaymentWebhook(new PaymentGatewayWebhook([
                        'payload' => $payload,
                        'signature' => $signature,
                        'webhookSecret' => $paymentGateway->webhook_secret,
                        'paymentGatewaySecret' => $paymentGateway->secret,
                    ]));
                $userOrderId = $paymentGatewayWebhook->userOrderId;
            } else {
                throw new IllegalArgumentException('payment_gateway_id', 'invalid payment gateway');
            }

//            /**
//             * For local env test, change to proper user_order_payment_id
//             */
//            if (env('APP_ENV') == 'local') {
//                $userOrderId = UserOrder::find(34);
//            }

            if (empty($userOrderId)) {
                Log::info('no user order found with webhook');
                throw new IllegalArgumentException('intent_id', 'intent id not found');
            }

            return UserOrderPayment::create([
                'user_order_id' => $userOrderId,
                'payment_gateway_id' => $paymentGateway->id,
                'amount' => $paymentGatewayWebhook->amount,
                'action' => UserOrderPayment::ACTION_WEBHOOK,
                'stripe_intent_id' => $paymentGatewayWebhook->paymentIntentId,
                'status' => $paymentGatewayWebhook->status,
                'request' => null,
                'response' => $paymentGatewayWebhook->response,
            ]);
        } catch (\Exception $e) {
            throw new IllegalArgumentException("payment gateway webhook error: " . $e->getMessage());
        }
    }
}
