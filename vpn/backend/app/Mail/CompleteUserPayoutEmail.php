<?php

namespace App\Mail;

use App\Models\UserPayout;
use App\Utils\Utilities;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CompleteUserPayoutEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private UserPayout $userPayout;

    public function __construct(UserPayout $userPayout)
    {
        $this->userPayout = $userPayout;
    }

    public function build() {
        $amount = Utilities::formatCurrency($this->userPayout->currency_name, $this->userPayout->amount_cents);
        return $this->markdown('emails.payout.complete')
            ->subject('您的' . config('app.name') . '收益 - ' . $amount)
            ->with([
                'user' => $this->userPayout->user->nickname,
                'orderUserNickname' => Str::mask($this->userPayout->order_user_nickname, '*', 1),
                'userPayoutAmount' => $amount,
                'userPayoutDate' => $this->userPayout->created_at_formatted,
                'userPayoutUrl' => config('app.frontend_url') . '/account/share/payouts',
            ]);
    }

}
