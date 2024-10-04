<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/auth/captcha', [AuthController::class, 'captcha']);
Route::post('/auth/register', [AuthController::class, 'createUser']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::put('/user/auth/reset', [UserController::class, 'confirmResetPassword']);


Route::prefix('ext')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'createUserFromApi']);
    Route::post('/auth/login', [AuthController::class, 'loginFromApi']);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::put('/user/auth', [UserController::class, 'updateAuth']);
});
