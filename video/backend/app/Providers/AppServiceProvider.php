<?php

namespace App\Providers;

use App\Utils\Md5Hasher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Schema::defaultStringLength(191);
        Hash::extend('md5', static function () {
            return new Md5Hasher();
        });

        if (env('APP_DEBUG')) {
            DB::listen(function ($query) {
                // Log query and bindings
                Log::info($query->sql, $query->bindings);
            });
        }
    }
}
