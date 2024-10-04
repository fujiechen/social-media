<?php

namespace App\Events;


use App\Services\AccountService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountTypeSavedEventHandler implements ShouldQueue
{
    private AccountService $accountService;

    public function __construct(AccountService $accountService) {
        $this->accountService = $accountService;
    }

    public function handle(AccountTypeSavedEvent $event): void {
        $this->accountService->batchCreateMissingAccountFromContact();
    }
}
