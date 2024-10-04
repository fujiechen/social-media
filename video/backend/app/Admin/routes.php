<?php

use App\Admin\Controllers\ActorController;
use App\Admin\Controllers\AjaxController;
use App\Admin\Controllers\AlbumController;
use App\Admin\Controllers\AlbumQueueController;
use App\Admin\Controllers\CategoryController;
use App\Admin\Controllers\DashboardController;
use App\Admin\Controllers\FileController;
use App\Admin\Controllers\JobController;
use App\Admin\Controllers\MediaController;
use App\Admin\Controllers\MediaQueueController;
use App\Admin\Controllers\MetaController;
use App\Admin\Controllers\OrderController;
use App\Admin\Controllers\ProductController;
use App\Admin\Controllers\ResourceActorController;
use App\Admin\Controllers\ResourceAlbumController;
use App\Admin\Controllers\ResourceCategoryController;
use App\Admin\Controllers\ResourceController;
use App\Admin\Controllers\ResourceTagController;
use App\Admin\Controllers\ResourceVideoController;
use App\Admin\Controllers\PlaylistQueueController;
use App\Admin\Controllers\SeriesQueueController;
use App\Admin\Controllers\UserActivityController;
use App\Admin\Controllers\UserCommentController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\UserFavoriteController;
use App\Admin\Controllers\UserHistoryController;
use App\Admin\Controllers\UserLikeController;
use App\Admin\Controllers\UserPayoutController;
use App\Admin\Controllers\UserReferralController;
use App\Admin\Controllers\UserShareController;
use App\Admin\Controllers\UserSubscriptionController;
use App\Admin\Controllers\VideoQueueController;
use App\Admin\Controllers\SeriesController;
use App\Admin\Controllers\TagController;
use App\Admin\Controllers\VideoController;
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

    $router->resource('media', MediaController::class);
    $router->resource('video', VideoController::class);
    $router->resource('series', SeriesController::class);
    $router->resource('album', AlbumController::class);
    $router->resource('tag', TagController::class);
    $router->resource('actor', ActorController::class);
    $router->resource('category', CategoryController::class);

    $router->resource('resource', ResourceController::class);
    $router->resource('resourceTag', ResourceTagController::class);
    $router->resource('resourceActor', ResourceActorController::class);
    $router->resource('resourceCategory', ResourceCategoryController::class);

    $router->resource('mediaQueue', MediaQueueController::class);
    $router->resource('videoQueue', VideoQueueController::class);
    $router->resource('albumQueue', AlbumQueueController::class);
    $router->resource('seriesQueue', SeriesQueueController::class);
    $router->resource('playlistQueue', PlaylistQueueController::class);

    $router->resource('resourceVideo', ResourceVideoController::class);
    $router->resource('resourceAlbum', ResourceAlbumController::class);
    $router->resource('file', FileController::class);
    $router->any('ajax/upload/', [AjaxController::class, 'upload']);

    $router->resource('product', ProductController::class);
    $router->resource('order', OrderController::class);
    $router->resource('userPayout', UserPayoutController::class);

    $router->resource('user', UserController::class);
    $router->resource('userActivity', UserActivityController::class);
    $router->resource('userReferral', UserReferralController::class);
    $router->resource('userSubscription', UserSubscriptionController::class);
    $router->resource('userFavorite', UserFavoriteController::class);
    $router->resource('userLike', UserLikeController::class);
    $router->resource('userHistory', UserHistoryController::class);
    $router->resource('userShare', UserShareController::class);
    $router->resource('userComment', UserCommentController::class);

    $router->resource('job', JobController::class);
    $router->resource('meta', MetaController::class);

});
