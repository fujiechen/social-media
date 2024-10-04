<?php

namespace App\Services\PaymentGateway;

use App\Dtos\PaymentGatewayIntent;
use App\Dtos\PaymentGatewayWebhook;
use App\Exceptions\IllegalArgumentException;
use App\Models\UserOrderPayment;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentGatewayService implements PaymentGatewayServiceInterface
{
    public function createPaymentIntent(PaymentGatewayIntent $paymentGatewayIntent): PaymentGatewayIntent
    {
        try {
            $stripe = new StripeClient($paymentGatewayIntent->paymentGatewaySecret);
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $paymentGatewayIntent->amount,
                'currency' => $paymentGatewayIntent->currencyName,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            $response = $paymentIntent->jsonSerialize();
            $stripeIntentId = $paymentIntent->id;
            $stripeIntentClientSecret = $paymentIntent->client_secret;
            $status = UserOrderPayment::STATUS_SUCCESSFUL;
        } catch (\Exception $e) {
            $response = $e->getTrace();
            $stripeIntentId = null;
            $stripeIntentClientSecret = null;
            $status = UserOrderPayment::STATUS_FAILED;
        }

        $paymentGatewayIntent->paymentIntentId = $stripeIntentId;
        $paymentGatewayIntent->clientSecret = $stripeIntentClientSecret;
        $paymentGatewayIntent->userOrderStatus = $status;
        $paymentGatewayIntent->response = $response;

        return $paymentGatewayIntent;
    }

    /**
     * @throws SignatureVerificationException
     * @throws IllegalArgumentException
     */
    public function processPaymentWebhook(PaymentGatewayWebhook $paymentGatewayWebhook): PaymentGatewayWebhook
    {
        $event = Webhook::constructEvent($paymentGatewayWebhook->payload,
            $paymentGatewayWebhook->signature, $paymentGatewayWebhook->webhookSecret);

        $data = $event->data->toArray();

        Log::info('processing stripe event data: ' . json_encode($data));

        $stripeIntentId = $event->data->object->payment_intent;

        Log::info('processing stripe intent id ' . $stripeIntentId);

        $paymentGatewayWebhook->amount = $event->data->object->amount;

        Log::info('processing event type ' . $event->type);

        $status = match ($event->type) {
            'charge.succeeded', 'charge.captured' => UserOrderPayment::STATUS_SUCCESSFUL,
            'charge.expired', 'charge.failed' => UserOrderPayment::STATUS_FAILED,
            default => throw new IllegalArgumentException('event', 'Unexpected event type'),
        };

        $paymentGatewayWebhook->paymentIntentId = $stripeIntentId;
        $paymentGatewayWebhook->response = $data;
        $paymentGatewayWebhook->status = $status;

        Log::info('build webhook: ' . json_encode($paymentGatewayWebhook));

        return $paymentGatewayWebhook;
    }
}
