<?php

namespace App\Services;

use App\Dtos\UserAccountDto;
use App\Exceptions\IllegalArgumentException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserPayout;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;

class PaymentService
{
    private PaymentGatewayService $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService) {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    /**
     * @param int $userId
     * @param string|null $currencyName
     * @return Collection<UserAccountDto>
     * @throws AuthenticationException
     * @throws IllegalArgumentException
     */
    public function getUserAccount(int $userId, ?string $currencyName): Collection {
        try {
            /**
             * @var User $user
             */
            $user = User::find($userId);
            $response = $this->paymentGatewayService->getUserAccount($user->access_token, $currencyName);
        } catch (\Exception $e) {
            throw new IllegalArgumentException('userAccount', 'Can not connect to payment gateway');
        }

        if (!$response->successful()) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $data = $response->json('data');

        if (empty($data)) {
            throw new IllegalArgumentException('userAccount', 'No UserAccount Found');
        }

        $userAccounts = new Collection();
        foreach ($data as $item) {
            $userAccounts->add(new UserAccountDto([
                'userAccountId' => $item['id'],
                'accountNumber' => $item['account_number'],
                'balance' => $item['balance'],
                'currencyName' => $item['currency']['name'],
            ]));
        }

        return $userAccounts;
    }

    public function createUserPayoutPayment(int $userPayoutId): Payment {
        /**
         * @var UserPayout $userPayout
         */
        $userPayout = UserPayout::find($userPayoutId);

        if ($userPayout->status == UserPayout::STATUS_COMPLETED) {
            throw new IllegalArgumentException('userPayout.status', 'Duplicate user payout payment');
        }

        $receiver = $userPayout->user;
        $amount = $userPayout->amount_cents / 100;

        $request = [
            'amount' => $amount,
            'from_currency_name' => $userPayout->currency_name,
            'to_user_email' => $receiver->email,
            'to_user_name' => $receiver->nickname,
            'comment' => 'User Payout to ' . $userPayout->id,
        ];

        try {
            $response = $this->paymentGatewayService->transfer(env('WALLET_USER_JWT'),
                $request['from_currency_name'], $request['amount'],
                $request['to_user_email'], $request['to_user_name'],
                $request['comment']);

            $data = $response->json('data');

            if ($data) {
                $status = $data['status'] ?? Payment::STATUS_FAILED;
            } else {
                $status = Payment::STATUS_FAILED;
                $data = [$response->status()];
            }
        } catch (ConnectionException $e) {
            $data = ['Bank server died'];
            $status = Payment::STATUS_FAILED;
        } catch (\Exception $e) {
            $data = $e->getTrace();
            $status = Payment::STATUS_FAILED;
        }

        return Payment::create([
            'user_payout_id' => $userPayoutId,
            'currency_name' => $userPayout->currency_name,
            'amount_cents' => $userPayout->amount_cents,
            'request' => $request,
            'response' => $data,
            'status' => $status,
        ]);
    }

    public function createOrderPayment(int $orderId): Payment {
        /**
         * @var Order $order
         */
        $order = Order::find($orderId);

        if ($order->status == Order::STATUS_COMPLETED) {
            throw new IllegalArgumentException('order.status', 'Duplicate order payment');
        }

        $amount = $order->total_cents / 100;

        $request = [
            'amount' => $amount,
            'from_currency_name' => $order->currency_name,
            'to_user_email' => env('WALLET_USER_EMAIL'),
            'to_user_name' => env('WALLET_USER_NAME'),
            'comment' => '支付订单: ' . $orderId,
        ];

        if ($amount > 0) {
            try {
                $response = $this->paymentGatewayService->transfer($order->user->access_token,
                    $request['from_currency_name'], $request['amount'],
                    $request['to_user_email'], $request['to_user_name'],
                    $request['comment']);

                $data = $response->json('data');

                if ($data) {
                    $status = $data['status'] ?? Payment::STATUS_FAILED;
                } else {
                    $body = json_decode($response->body(), true);
                    if(isset($body['errors']['user_account.balance'])) {
                        throw new IllegalArgumentException('user_account.balance', 'You have insufficient account balance');
                    }
                    $status = Payment::STATUS_FAILED;
                    $data = [$response->status()];
                }
            } catch (ConnectionException $e) {
                throw new IllegalArgumentException('connection', 'Connection failed');
            } catch (IllegalArgumentException $e) {
                throw new IllegalArgumentException('user_account.balance', 'You have insufficient account balance');
            } catch (\Exception $e) {
                throw new IllegalArgumentException('unknown', 'Unknown error');
            }
        } else {
            $data = [];
            $status = Payment::STATUS_SUCCESSFUL;
        }

        return Payment::create([
            'order_id' => $orderId,
            'currency_name' => $order->currency_name,
            'amount_cents' => $order->total_cents,
            'request' => $request,
            'response' => $data,
            'status' => $status,
        ]);
    }
}
