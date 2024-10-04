<?php

namespace App\Console\Commands;

use App\Events\TestEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    protected $signature = 'bank:test';

    public function handle(): void {
        // need to test db connection
        $totalUsers = User::query()->count();
        echo "this is a test command " . $totalUsers ;
        Log::info('test run ' . $totalUsers);
        Event::dispatch(new TestEvent());
    }
}
