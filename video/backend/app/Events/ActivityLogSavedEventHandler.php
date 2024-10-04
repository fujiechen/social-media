<?php

namespace App\Events;

use App\Services\UserService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityLogSavedEventHandler implements ShouldQueue
{
    private UserService $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function handle(ActivityLogSavedEvent $event): bool {

        return true;
    }

}
