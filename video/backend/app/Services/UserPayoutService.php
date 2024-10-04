<?php

namespace App\Services;

use App\Models\Meta;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\UserPayout;
use App\Models\UserReferral;
use Illuminate\Database\Eloquent\Builder;

class UserPayoutService
{
    private MetaService $metaService;

    public function __construct(MetaService $metaService)
    {
        $this->metaService = $metaService;
    }

    public function fetchAllUserPayoutsQuery(int $userId, string $status): Builder
    {
        return UserPayout::query()
            ->where('user_id', '=', $userId)
            ->where('status', '=', $status);
    }

    /**
     * - 新用户注册送积分
     * - 新用户推荐送积分
     *
     * @param int $newUserId
     * @return void
     */
    public function completeNewUserPayout(int $newUserId): void
    {
        //give new user registration points
        $newUserRegistrationPoints = $this->metaService->getValue(Meta::NEW_USER_POINTS, 1000);
        UserPayout::create([
            'user_id' => $newUserId,
            'type' => UserPayout::TYPE_EARNING,
            'status' => UserPayout::STATUS_PENDING,
            'currency_name' => env('CURRENCY_POINTS'),
            'amount_cents' => $newUserRegistrationPoints,
            'comment' => '新用户注册',
        ]);

        //give parent user referral points
        $parentUserReferral = UserReferral::query()
            ->where('sub_user_id', '=', $newUserId)
            ->where('level', '=', 1)
            ->first();

        if ($parentUserReferral) {
            $newUserReferralPoints = $this->metaService->getValue(Meta::USER_REFERRAL_POINTS, 1000);
            UserPayout::create([
                'user_id' => $parentUserReferral->user_id,
                'type' => UserPayout::TYPE_EARNING,
                'status' => UserPayout::STATUS_PENDING,
                'currency_name' => env('CURRENCY_POINTS'),
                'amount_cents' => $newUserReferralPoints,
                'comment' => '新用户注册推荐',
            ]);
        }
    }

    /**
     * - 分配商品所有者的钱
     * - 商品购买者的上线分成
     *
     * Transfer commission from video user to media owner
     * - pay 50% to product owner
     * - pay 10% to user's referral
     * - each referral got $total / $level * $totalParentUsers,
     *
     *
     * @param int $orderProductId
     * @return void
     */
    private function processOrderProductPayout(int $orderProductId): void
    {
        /**
         * @var OrderProduct $orderProduct
         */
        $orderProduct = OrderProduct::find($orderProductId);

        //give commissions to cash only
        if ($orderProduct->currency_name != env('CURRENCY_CASH')) {
            return;
        }

        $payerId = $orderProduct->order->user_id;
        $amountCents = $orderProduct->unit_cents * $orderProduct->qty;

        $receiverId = $orderProduct->product->user_id;

        $comment = '购买商品分成: ' . $orderProduct->product->name;

        if (!empty($receiverId)) {
            $percentage = (float) $this->metaService->getValue(Meta::PRODUCT_OWNER_PERCENTAGE, 0.5);
            UserPayout::create([
                'user_id' => $receiverId,
                'order_product_id' => $orderProductId,
                'type' => UserPayout::TYPE_EARNING,
                'status' => UserPayout::STATUS_PENDING,
                'currency_name' => env('CURRENCY_CASH'),
                'amount_cents' => $amountCents * $percentage,
                'comment' => $comment,
            ]);
        }

        $referralCommission = $amountCents * 0.1;
        $this->processReferralCommissionPayout($payerId, $referralCommission, $comment, $orderProductId);
    }

    /**
     * 购买商品的上线用户分成
     *
     * @param int $targetUserId
     * @param int $totalCommissionCents
     * @param string $comment
     * @param int|null $orderProductId
     * @return void
     */
    public function processReferralCommissionPayout(int $targetUserId, int $totalCommissionCents, string $comment, ?int $orderProductId): void
    {
        $userReferrals = UserReferral::query()->where('sub_user_id', '=', $targetUserId)->get();

        $totalParentUsers = $userReferrals->count();

        if (!$totalParentUsers) {
            return;
        }

        /**
         * @var UserReferral $userReferral
         */
        foreach ($userReferrals as $userReferral) {
            UserPayout::create([
                'user_id' => $userReferral->user_id,
                'type' => UserPayout::TYPE_COMMISSION,
                'status' => UserPayout::STATUS_PENDING,
                'currency_name' => env('CURRENCY_CASH'),
                'amount_cents' => $totalCommissionCents / ($userReferral->level * $totalParentUsers),
                'order_product_id' => $orderProductId,
                'comment' => $comment,
            ]);
        }

    }

    /**
     *
     * @param int $orderId
     * @return void
     */
    public function processCompletedOrderPayout(int $orderId): void
    {
        /**
         * @var Order $order
         */
        $order = Order::find($orderId);

        if ($order->status != Order::STATUS_COMPLETED) {
            return;
        }

        foreach ($order->orderProducts as $orderProduct) {
            $this->processOrderProductPayout($orderProduct->id);
        }
    }

    public function getTotalSuccessfulPayout(int $userId, string $currencyName): float
    {
        return UserPayout::query()
                ->where('user_id', $userId)
                ->where('currency_name', $currencyName)
                ->where('status', UserPayout::STATUS_COMPLETED)
                ->sum('amount_cents') / 100;
    }

}
