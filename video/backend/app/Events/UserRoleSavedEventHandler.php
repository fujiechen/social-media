<?php

namespace App\Events;

use App\Services\UserPayoutService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserRoleSavedEventHandler implements ShouldQueue
{
    public UserPayoutService $userPayoutService;

    public function __construct(UserPayoutService $userPayoutService) {
        $this->userPayoutService = $userPayoutService;
    }

    public function handle(UserRoleSavedEvent $event): void {
    }
}
