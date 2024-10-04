<?php

use App\Admin\Controllers\AccountController;
use App\Admin\Controllers\AccountTypeController;
use App\Admin\Controllers\AjaxController;
use App\Admin\Controllers\ContactController;
use App\Admin\Controllers\ContentTypeController;
use App\Admin\Controllers\DashboardController;
use App\Admin\Controllers\FileController;
use App\Admin\Controllers\LandingController;
use App\Admin\Controllers\LandingDomainController;
use App\Admin\Controllers\PostController;
use App\Admin\Controllers\LandingTemplateController;
use App\Admin\Controllers\RedirectTypeController;
use App\Admin\Controllers\TargetUrlController;
use App\Admin\Controllers\UserController;
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

    $router->any('ajax/upload/', [AjaxController::class, 'upload']);
    $router->get('/', [DashboardController::class, 'index']);
    $router->resource('user', UserController::class);
    $router->resource('file', FileController::class);
    $router->resource('contact', ContactController::class);

    $router->resource('contentType', ContentTypeController::class);
    $router->resource('redirectType', RedirectTypeController::class);
    $router->resource('accountType', AccountTypeController::class);
    $router->resource('landingDomain', LandingDomainController::class);
    $router->resource('targetUrl', TargetUrlController::class);
    $router->resource('account', AccountController::class);
    $router->resource('landingTemplate', LandingTemplateController::class);
    $router->resource('post', PostController::class);
    $router->resource('landing', LandingController::class);

});
