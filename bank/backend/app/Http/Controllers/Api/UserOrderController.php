<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\CreateDepositOrderRequest;
use App\Http\Requests\CreateExchangeOrderRequest;
use App\Http\Requests\CreatePurchaseOrderRequest;
use App\Http\Requests\CreateTransferOrderRequest;
use App\Http\Requests\CreateWithdrawOrderRequest;
use App\Http\Requests\IndexUserOrderRequest;
use App\Http\Resources\ReceiverResource;
use App\Http\Resources\UserOrderResource;
use App\Models\User;
use App\Services\UserAccountService;
use App\Services\UserOrderPaymentService;
use App\Services\UserOrderService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Api
 */
class UserOrderController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    private UserOrderService $userOrderService;
    private UserOrderPaymentService $userOrderPaymentService;
    private UserAccountService $userAccountService;
    private UserService $userService;

    public function __construct(UserOrderService $userOrderService, UserOrderPaymentService $userOrderPaymentService, UserAccountService $userAccountService, UserService $userService)
    {
        $this->userOrderService = $userOrderService;
        $this->userOrderPaymentService = $userOrderPaymentService;
        $this->userAccountService = $userAccountService;
        $this->userService = $userService;
    }

    /**
     * @throws IllegalArgumentException
     */
    public function createDepositOrder(CreateDepositOrderRequest $request): UserOrderResource
    {
        Log::info('createDepositOrder start, user_account_id:' . $request->input('user_account_id'));
        if ($request->currency_name) {
            $userAccount = $this->userAccountService->getUserAccountQuery(Auth::user()->id, $request->currency_name)->first();
            if (empty($userAccount)) {
                throw new IllegalArgumentException('userAccount.currency_name', 'User Account not found');
            }
            $userAccountId = $userAccount->id;
        } else {
            $userAccountId = $request->user_account_id;
        }

        Log::info('createDepositOrder start calling userOrderService, user_account_id:' . $request->input('user_account_id'));
        $userOrder = $this->userOrderService
            ->createDepositOrder(
                $userAccountId,
                $request->amount,
                $request->comment ?? null,
                $request->meta_json ?? null,
            );

        Log::info('createDepositOrder start calling createUserOrderPayment, user_order_id:' . $userOrder->id);
        $userOrderPayment = $this->userOrderPaymentService
            ->createUserOrderPayment(
                $userOrder->id,
                $request->payment_method,
                $request->callback_url,
            );

        Log::info('createDepositOrder start calling createUserOrderPayment, user_order_id:' . $userOrder->id . 'user_order_payment_id:' . $userOrderPayment->id);
        $userOrder->refresh();
        Log::info('createDepositOrder user order refreshed, user_order_id:' . $userOrder->id);
        return new UserOrderResource($userOrder);
    }

    /**
     * @throws \App\Exceptions\IllegalArgumentException
     */
    public function createPurchaseOrder(CreatePurchaseOrderRequest $request): UserOrderResource
    {
        /**
         * @var User $user
         */
        $user = Auth::user();
        $userOrder = $this->userOrderService
            ->createPurchaseOrder($user->id, $request->product_id, $request->amount, $request->comment);

        return new UserOrderResource($userOrder);
    }

    /**
     * @throws \App\Exceptions\IllegalArgumentException
     */
    public function createTransferOrder(CreateTransferOrderRequest $request): UserOrderResource
    {
        $fromUserAccountId = $request->from_user_account_id;
        if ($request->from_currency_name) {
            $userAccount = $this->userAccountService->getUserAccountQuery(Auth::user()->id, $request->from_currency_name)->first();
            if (empty($userAccount)) {
                throw new IllegalArgumentException('userAccount.currency_name', 'User Account not found');
            }
            $fromUserAccountId = $userAccount->id;
        }
        /**
         * @var User $toUser
         */
        $toUser = User::query()
            ->where('email', '=', $request->input('to_user_email'))
            ->where('nickname', '=', $request->input('to_user_name'))
            ->first();

        if (empty($toUser) && ($accessToken = $request->input('to_user_access_token'))) {
            Log::info('transfer to a new created user from another system, create this user first');
            $payload = JWTAuth::setToken($accessToken)->getPayload();
            $userData = $payload->toArray()['user'];
            $userId = $userData['id'];
            $userAgentId = $userData['extras']['user_agent_id'] ?? null;
            $user = $this->userService->create($userId, $accessToken, $userData['nickname'],
                $userData['email'], $userData['password'], $userData['username'],
                $userData['language'], [], $userAgentId, $userData['phone'] ?? null, $userData['wechat'] ?? null,
                $userData['whatsapp'] ?? null, $userData['alipay'] ?? null, $userData['telegram'] ?? null, $userData['facebook'] ?? null);
            Log::info('created new user ' . $user->id);
        }

        $userOrder = $this->userOrderService
            ->createTransferOrder(
                $fromUserAccountId,
                $request->to_user_email,
                $request->to_user_name,
                $request->amount,
                $request->comment ?? null,
                $request->meta_json ?? null,
            );

        return new UserOrderResource($userOrder);
    }

    public function createExchangeOrder(CreateExchangeOrderRequest $request): UserOrderResource
    {
        $userOrder = $this->userOrderService
            ->createExchangeOrder($request->from_user_account_id, $request->to_user_account_id, $request->amount);

        return new UserOrderResource($userOrder);
    }

    public function createWithdrawOrder(CreateWithdrawOrderRequest $request): UserOrderResource
    {
        $userOrder = $this->userOrderService
            ->createWithdrawOrder($request->user_account_id, $request->amount, $request->user_address_id, $request->user_withdraw_account_id);

        return new UserOrderResource($userOrder);
    }

    public function index(IndexUserOrderRequest $request): AnonymousResourceCollection
    {
        $user = Auth::user();
        $userAccountId = $request->get('user_account_id', null);
        $type = $request->get('order_type', null);
        $productId = $request->get('product_id', null);
        $limit = $request->get('limit', self::ITEM_PER_PAGE);

        $query = $this->userOrderService->getOrdersQuery($user->id, $userAccountId, $type, $productId);

        return UserOrderResource::collection($query->paginate($limit));
    }

    public function indexTransferredUsers(Request $request): AnonymousResourceCollection
    {
        $limit = $request->get('limit', self::ITEM_PER_PAGE);
        $user = Auth::user();
        $query = $this->userOrderService->getTransferredUsersQuery($user->id);
        return ReceiverResource::collection($query->paginate($limit));
    }

}
