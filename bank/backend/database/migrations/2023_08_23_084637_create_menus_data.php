<?php

use App\Models\Role;
use App\Services\UserMenuService;
use App\Services\UserService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * @var UserMenuService $userMenuService
         */
        $userMenuService = app(UserMenuService::class);

        $userMenuService->createMenuGroup('Configuration Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration Manager','Currencies', 'currency/', 'currency/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration Manager','Exchange Rates', 'currencyRate/', 'currencyRate/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Configuration Manager','Payment Gateways', 'paymentGateway/', 'paymentGateway/*');

        $userMenuService->createMenuGroup('User Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Profiles', 'user/', 'user/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Activities', 'userActivity/', 'userActivity/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Accounts', 'userAccount/', 'userAccount/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Withdraws', 'userWithdrawAccount/', 'userWithdrawAccount/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('User Manager','Addresses', 'userAddress/', 'userAddress/*');


        $userMenuService->createMenuGroup('Product Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Product Manager','Categories', 'productCategory/', 'productCategory/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Product Manager','Products', 'product/', 'product/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Product Manager','Daily Rates', 'productRate/', 'productRate/*');

        $userMenuService->createMenuGroup('Order Manager');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Deposit Orders', 'order/deposit', 'order/deposit/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Purchase Orders', 'order/purchase', 'order/purchase/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Withdraw Orders', 'order/withdraw', 'order/withdraw/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Exchange Orders', 'order/exchange', 'order/exchange/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Transfer Orders', 'order/transfer', 'order/transfer/*');
        $userMenuService->createMenuAndPermissionToMenuGroup('Order Manager','Investments', 'userProduct/', 'userProduct/*');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
