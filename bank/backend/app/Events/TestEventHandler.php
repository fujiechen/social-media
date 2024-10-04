<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestEventHandler implements ShouldQueue
{
    use SerializesModels;

    public function handle(): void {
        $totalUsers = User::query()->count();
        Log::info('handle test event: ' . $totalUsers);
        echo "queue event: " . $totalUsers;
    }
}
