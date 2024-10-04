<?php

namespace App\Events;


use App\Services\AccountService;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactSavedEventHandler implements ShouldQueue
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService) {
        $this->accountService = $accountService;
    }

    public function handle(ContactSavedEvent $event): void {
        $this->accountService->batchCreateMissingAccountFromContact();
    }
}
