<?php

namespace App\Events;

use App\Models\UserOrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class UserOrderNotificationCreatedEvent implements ShouldQueue
{
    use SerializesModels;

    public UserOrderNotification $userOrderNotification;

    public function __construct(UserOrderNotification $userOrderNotification) {
        $this->userOrderNotification = $userOrderNotification;
    }
}
