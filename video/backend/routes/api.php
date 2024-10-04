<?php

use App\Http\Controllers\AlbumQueueController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MediaCommentController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaFavoriteController;
use App\Http\Controllers\MediaHistoryController;
use App\Http\Controllers\MediaLikeController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PlaylistQueueController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\UserShareController;
use App\Http\Controllers\UserStatisticsController;
use App\Http\Controllers\UserSubscriptionController;
use App\Http\Controllers\VideoQueueController;
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
Route::get('metas', [MetaController::class, 'index']);

Route::get('medias', [MediaController::class, 'index']);
Route::get('medias/recommendation', [MediaController::class, 'recommendation']);
Route::get('medias/tags', [MediaController::class, 'tags']);
Route::get('medias/tags/{id}', [MediaController::class, 'tag']);
Route::get('medias/categories', [MediaController::class, 'categories']);
Route::get('medias/categories/{id}', [MediaController::class, 'category']);
Route::get('medias/actors', [MediaController::class, 'actors']);
Route::get('medias/actors/{id}', [MediaController::class, 'actor']);
Route::get('medias/users', [MediaController::class, 'users']);
Route::get('medias/users/{id}', [MediaController::class, 'user']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{productId}', [ProductController::class, 'show']);
Route::get('user/shares/{userShareId}/qrCode', [UserShareController::class, 'qrCode']);

Route::get('medias/{mediaId}/products', [ProductController::class, 'mediaProducts'])->where(['mediaId' => '[0-9]+']);
Route::get('medias/{mediaId}/similar', [MediaController::class, 'similar'])->where(['mediaId' => '[0-9]+']);

Route::get('medias/{mediaId}', [MediaController::class, 'show'])->where(['mediaId' => '[0-9]+']);
Route::get('files/{id}/m3u8', [FileController::class, 'm3u8']);
Route::get('searches/hot', [UserSearchController::class, 'hot']);

Route::get('medias/{mediaId}/comments', [MediaCommentController::class, 'index']);

Route::get('user/payouts/others', [UserController::class, 'otherUserPayouts']);

Route::group(['middleware' => ['auth.union', 'auth:api', 'audit']], function () {
    Route::get('user', [UserController::class, 'show']);
    Route::get('user/statistics', [UserStatisticsController::class, 'show']);

    Route::post('user/shares', [UserShareController::class, 'store']);
    Route::get('user/shares/{userShareId}', [UserShareController::class, 'show']);
    Route::get('user/shares', [UserShareController::class, 'index']);


    Route::get('searches/history', [UserSearchController::class, 'history']);
    Route::get('user/subscriptions', [UserSubscriptionController::class, 'subscriptions']);
    Route::get('user/subscriptions/subscribers', [UserSubscriptionController::class, 'followers']);
    Route::get('user/subscriptions/friends', [UserSubscriptionController::class, 'friends']);
    Route::get('user/subscriptions/medias', [UserSubscriptionController::class, 'medias']);

    Route::post('user/subscriptions/{publisherUserId}', [UserSubscriptionController::class, 'subscribe']);
    Route::delete('user/subscriptions/{publisherUserId}', [UserSubscriptionController::class, 'deleteSubscription']);

    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{orderId}', [OrderController::class, 'show']);
    Route::post('orders/instant', [OrderController::class, 'instantPayment']);

    Route::get('medias/comments/medias', [MediaCommentController::class, 'medias']);
    Route::post('medias/{mediaId}/comments', [MediaCommentController::class, 'store']);
    Route::get('medias/{mediaId}/comments/{mediaCommentId}', [MediaCommentController::class, 'show']);
    Route::put('medias/{mediaId}/comments', [MediaCommentController::class, 'update']);
    Route::delete('medias/{mediaId}/comments', [MediaCommentController::class, 'destroy']);

    Route::get('medias/favorites', [MediaFavoriteController::class, 'index']);
    Route::post('medias/favorites/{mediaId}', [MediaFavoriteController::class, 'toggle']);

    Route::get('medias/likes', [MediaLikeController::class, 'index']);
    Route::post('medias/likes/{mediaId}', [MediaLikeController::class, 'toggleLike']);
    Route::post('medias/dislikes/{mediaId}', [MediaLikeController::class, 'toggleDislike']);

    Route::get('medias/histories', [MediaHistoryController::class, 'index']);


    Route::get('video/queues', [VideoQueueController::class, 'index']);
    Route::post('video/queues', [VideoQueueController::class, 'store']);
    Route::post('video/queues/{id}/started', [VideoQueueController::class, 'updateStatusToStarted']);
    Route::post('video/queues/{id}/error', [VideoQueueController::class, 'updateStatusToError']);
    Route::post('video/queues/{id}/completed', [VideoQueueController::class, 'updateStatusToCompleted']);


    Route::get('album/queues', [AlbumQueueController::class, 'index']);
    Route::post('album/queues', [AlbumQueueController::class, 'store']);
    Route::post('album/queues/{id}/started', [AlbumQueueController::class, 'updateStatusToStarted']);
    Route::post('album/queues/{id}/error', [AlbumQueueController::class, 'updateStatusToError']);
    Route::post('album/queues/{id}/completed', [AlbumQueueController::class, 'updateStatusToCompleted']);

    Route::get('playlist/queues', [PlaylistQueueController::class, 'index']);
    Route::post('playlist/queues/{id}/started', [PlaylistQueueController::class, 'updateStatusToStarted']);
    Route::post('playlist/queues/{id}/error', [PlaylistQueueController::class, 'updateStatusToError']);
    Route::post('playlist/queues/{id}/completed', [PlaylistQueueController::class, 'updateStatusToCompleted']);

    Route::get('user/children', [UserController::class, 'children']);
    Route::get('user/children/orders', [UserController::class, 'childrenCompletedOrders']);
    Route::get('user/payouts', [UserController::class, 'completedUserPayouts']);

    //bank bridge service
    Route::get('user/payment/balance', [OrderPaymentController::class, 'balance']);
    Route::post('orders/{orderId}/payment/create', [OrderPaymentController::class, 'store']);

    //File uploader
    Route::get('upload/urls', [FileController::class, 'getPresignedUrl']);
});

