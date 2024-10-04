<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\UserPayout;
use App\Models\UserReferral;
use Illuminate\Database\Eloquent\Builder;

class UserPayoutService
{
    public function fetchAllUserPayoutsQuery(int $userId, string $status): Builder
    {
        return UserPayout::query()
            ->where('user_id', '=', $userId)
            ->where('status', '=', $status);
    }

    public function completeNewUserPayout(int $newUserId): void
    {
        //no new user commissions
    }

    /**
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

        //no 0 payouts
        if ($amountCents === 0) {
            return;
        }

        $comment = '推广佣金';
        $referralCommission = $amountCents * 0.3;
        $this->processReferralCommissionPayout($payerId, $referralCommission, $comment, $orderProductId);
    }

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

    public function getTotalSuccessfulPayoutInCents(int $userId, string $currencyName): float
    {
        return UserPayout::query()
                ->where('user_id', $userId)
                ->where('currency_name', $currencyName)
                ->where('status', UserPayout::STATUS_COMPLETED)
                ->sum('amount_cents');
    }

}
