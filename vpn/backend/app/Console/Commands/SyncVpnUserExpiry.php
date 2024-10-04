<?php

namespace App\Console\Commands;

use App\Events\CategoryUserUpdatedEvent;
use App\Mail\CategoryExpiryEmail;
use App\Models\CategoryUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SyncVpnUserExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vpn:sync-vpn-user-expiry';

    protected $description = 'Sync category user expiry status with vpn server';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        CategoryUser::query()->chunk(100, function ($categoryUsers) {
            /**
             * @var CategoryUser $categoryUser
             */
            foreach ($categoryUsers as $categoryUser) {
                event(new CategoryUserUpdatedEvent($categoryUser));
                if ($categoryUser->valid_until_at_days === 3) {
                    Mail::to($categoryUser->user->email)->queue(new CategoryExpiryEmail($categoryUser, 3));
                } else if ($categoryUser->valid_until_at_days === 2) {
                    Mail::to($categoryUser->user->email)->queue(new CategoryExpiryEmail($categoryUser, 2));
                } else if ($categoryUser->valid_until_at_days === 1) {
                    Mail::to($categoryUser->user->email)->queue(new CategoryExpiryEmail($categoryUser, 1));
                } else if ($categoryUser->valid_until_at_days === 0) {
                    Mail::to($categoryUser->user->email)->queue(new CategoryExpiryEmail($categoryUser, 0));
                }
            }
        });
    }
}
