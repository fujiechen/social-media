<?php

use App\Admin\Controllers\AjaxController;
use App\Admin\Controllers\AppCategoryController;
use App\Admin\Controllers\AppController;
use App\Admin\Controllers\CategoryController;
use App\Admin\Controllers\CategoryUserController;
use App\Admin\Controllers\DashboardController;
use App\Admin\Controllers\FileController;
use App\Admin\Controllers\JobController;
use App\Admin\Controllers\MetaController;
use App\Admin\Controllers\OrderController;
use App\Admin\Controllers\ProductController;
use App\Admin\Controllers\ServerController;
use App\Admin\Controllers\ServerUserController;
use App\Admin\Controllers\TutorialController;
use App\Admin\Controllers\UserActivityController;
use App\Admin\Controllers\UserController;
use App\Admin\Controllers\UserPayoutController;
use App\Admin\Controllers\UserReferralController;
use App\Admin\Controllers\UserShareController;
use App\Models\AppCategory;
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
    $router->resource('category', CategoryController::class);
    $router->resource('server', ServerController::class);
    $router->resource('serverUser', ServerUserController::class);
    $router->resource('categoryUser', CategoryUserController::class);

    $router->resource('file', FileController::class);
    $router->any('ajax/upload/', [AjaxController::class, 'upload']);

    $router->resource('product', ProductController::class);
    $router->resource('order', OrderController::class);
    $router->resource('userPayout', UserPayoutController::class);
    $router->resource('userShare', UserShareController::class);

    $router->resource('user', UserController::class);
    $router->resource('userActivity', UserActivityController::class);
    $router->resource('userReferral', UserReferralController::class);

    $router->resource('job', JobController::class);

    $router->resource('meta', MetaController::class);
    $router->resource('tutorial', TutorialController::class);
    $router->resource('appCategory', AppCategoryController::class);
    $router->resource('app', AppController::class);

});
