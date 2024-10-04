<?php

use App\Admin\Controllers\CurrencyController;
use App\Admin\Controllers\CurrencyRateController;
use App\Admin\Controllers\DashboardController;
use App\Admin\Controllers\PaymentGatewayController;
use App\Admin\Controllers\ProductCategoryController;
use App\Admin\Controllers\ProductController;
use App\Admin\Controllers\ProductRateController;
use App\Admin\Controllers\SettingController;
use App\Admin\Controllers\UserAccountController;
use App\Admin\Controllers\UserActivityController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\UserDepositOrderController;
use App\Admin\Controllers\UserExchangeOrderController;
use App\Admin\Controllers\UserOrderPaymentController;
use App\Admin\Controllers\UserPurchaseOrderController;
use App\Admin\Controllers\UserTransactionController;
use App\Admin\Controllers\UserTransferOrderController;
use App\Admin\Controllers\UserWithdrawOrderController;
use Dcat\Admin\Admin;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

//backend
Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', [DashboardController::class, 'index']);
    $router->resource('user', UserController::class);
    $router->resource('userTransaction', UserTransactionController::class);
    $router->resource('userActivity', UserActivityController::class);
    $router->resource('userAccount', UserAccountController::class);

    //configuration
    $router->resource('paymentGateway', PaymentGatewayController::class);
    $router->resource('currency', CurrencyController::class);
    $router->resource('currencyRate', CurrencyRateController::class);

    //products
    $router->resource('product', ProductController::class);
    $router->resource('productCategory', ProductCategoryController::class);
    $router->resource('productRate', ProductRateController::class);

    //order
    $router->resource('order/payment', UserOrderPaymentController::class);
    $router->resource('order/deposit', UserDepositOrderController::class);
    $router->resource('order/withdraw', UserWithdrawOrderController::class);
    $router->resource('order/purchase', UserPurchaseOrderController::class);
    $router->resource('order/exchange', UserExchangeOrderController::class);
    $router->resource('order/transfer', UserTransferOrderController::class);

    $router->resource('setting', SettingController::class);
});
