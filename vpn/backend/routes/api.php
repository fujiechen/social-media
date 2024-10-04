<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TutorialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserShareController;
use App\Http\Controllers\UserStatisticsController;
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
Route::post('test/post', [TestController::class, 'testPost']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{categoryId}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{productId}', [ProductController::class, 'show']);
Route::get('user/shares/{userShareId}/qrCode', [UserShareController::class, 'qrCode']);
Route::get('metas', [MetaController::class, 'index']);
Route::get('apps', [AppController::class, 'index']);
Route::get('apps/categories', [AppController::class, 'categories']);
Route::post('user/sendResetEmail', [UserController::class, 'sendResetPasswordEmail']);
Route::get('tutorials/{os}', [TutorialController::class, 'show']);
Route::get('server/connected', [ServerController::class, 'isAnyServerConnected']);

Route::group(['middleware' => ['auth.union', 'auth:api', 'audit']], function () {
    Route::get('user', [UserController::class, 'show']);
    Route::get('user/servers/{categoryId}', [UserController::class, 'servers']);
    Route::get('user/category', [UserController::class, 'userCategory']);

    //bank bridge service
    Route::get('user/statistics', [UserStatisticsController::class, 'show']);

    Route::post('user/shares', [UserShareController::class, 'store']);
    Route::get('user/shares/{userShareId}', [UserShareController::class, 'show']);
    Route::get('user/shares', [UserShareController::class, 'index']);


    Route::get('user/children', [UserController::class, 'children']);
    Route::get('user/children/orders', [UserController::class, 'childrenCompletedOrders']);
    Route::get('user/payouts', [UserController::class, 'completedUserPayouts']);
    Route::get('user/payouts/others', [UserController::class, 'otherUserPayouts']);

    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{orderId}', [OrderController::class, 'show']);

    Route::get('user/payment/balance', [OrderPaymentController::class, 'balance']);
    Route::post('orders/{orderId}/payment/create', [OrderPaymentController::class, 'store']);
});

