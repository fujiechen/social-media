<?php

namespace App\Events;

use App\Models\UserPayout;
use Illuminate\Queue\SerializesModels;

class UserPayoutSavedEvent
{
    use SerializesModels;

    public UserPayout $userPayout;

    public function __construct(UserPayout $userPayout) {
        $this->userPayout = $userPayout;
    }
}
