<?php

namespace App\Console\Commands;

use App\Models\UserOrder;
use App\Services\UserOrderService;
use Illuminate\Console\Command;

class CloseDepositOrderCommand extends Command
{
    protected $signature = 'bank:close-deposit-order';

    private UserOrderService $userOrderService;

    public function __construct(UserOrderService $userOrderService)
    {
        parent::__construct();
        $this->userOrderService = $userOrderService;
    }

    public function handle(): void {
        $this->userOrderService->getDepositOrdersToCloseQuery()
            ->each(function (UserOrder $userOrder) {
            $this->userOrderService
                ->updateUserOrder(UserOrderService::ACTION_COMPLETE_ORDER_AS_FAILED, $userOrder->id);
        });
    }
}
