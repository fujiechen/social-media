<?php

namespace App\Events;

use App\Models\UserPayout;
use App\Models\UserRole;
use Illuminate\Queue\SerializesModels;

class UserRoleSavedEvent
{
    use SerializesModels;

    public UserRole $userRole;

    public function __construct(UserRole $userRole) {
        $this->userRole = $userRole;
    }
}
