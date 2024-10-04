<?php

use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserOrderController;
use App\Http\Controllers\Api\UserProductController;
use App\Http\Controllers\Api\UserTransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('test/index', [TestController::class, 'index']);

Route::post('payment/webhook/{paymentGatewayId}', [PaymentController::class, 'webhook']);
Route::get('settings/index', [SettingController::class, 'index']);
Route::get('settings/{name}', [SettingController::class, 'show']);
Route::get('settings/currency/rate', [SettingController::class, 'currencyRate']);
Route::get('settings/user/order/types', [SettingController::class, 'userOrderType']);
Route::get('settings/user/order/statuses', [SettingController::class, 'userOrderStatus']);
Route::get('settings/user/transaction/types', [SettingController::class, 'userTransactionType']);
Route::get('settings/user/transaction/statuses', [SettingController::class, 'userTransactionStatus']);

Route::get('products/index', [ProductController::class, 'index']); //tested
Route::get('products/{id}', [ProductController::class, 'show']); //tested
Route::get('products/categories/index', [ProductController::class, 'categories']); //tested
Route::post('user/sendResetEmail', [UserController::class, 'sendResetPasswordEmail']);

Route::group(['middleware' => ['auth.union', 'auth:api', 'audit']], function () {

    //Frontend APIs
    Route::get('user/profile', [UserController::class, 'show']);  //tested
    Route::post('user/profile', [UserController::class, 'update']); //tested
    Route::get('user/accounts', [UserController::class, 'accounts']); //tested
    Route::post('user/support', [UserController::class, 'support']); //tested

    //user actions
    Route::post('user/orders/purchase', [UserOrderController::class, 'createPurchaseOrder']);
    Route::post('user/orders/deposit', [UserOrderController::class, 'createDepositOrder']);
    Route::post('user/orders/withdraw', [UserOrderController::class, 'createWithdrawOrder']);
    Route::post('user/orders/exchange', [UserOrderController::class, 'createExchangeOrder']);
    Route::post('user/orders/transfer', [UserOrderController::class, 'createTransferOrder']);

    //list all user orders
    Route::get('user/orders/index', [UserOrderController::class, 'index']);
    Route::get('user/orders/transfer/users', [UserOrderController::class, 'indexTransferredUsers']);

    //list all transactions
    Route::get('user/transactions/index', [UserTransactionController::class, 'index']);

    //list all user products
    Route::get('user/products/index', [UserProductController::class, 'index']);
    Route::get('user/products/{user_product_id}/returns', [UserProductController::class, 'indexProductReturns']);

    Route::apiResource('user/addresses', UserAddressController::class);
    Route::apiResource('user/withdraw/accounts', UserAddressController::class);
});

