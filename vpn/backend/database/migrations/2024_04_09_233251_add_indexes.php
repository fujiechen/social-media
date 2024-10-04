<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->index('app_category_id');
        });
        Schema::table('category_users', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('category_id');
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index('user_payout_id');
            $table->index('order_id');
            $table->index('status');
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('file_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
        });
        Schema::table('tutorial_files', function (Blueprint $table) {
            $table->index('tutorial_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->change();
            $table->string('email', 255)->change();
            $table->string('nickname', 255)->change();
            $table->index('username');
        });
        Schema::table('user_payouts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('order_product_id');
        });
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('sub_user_id');
        });
        Schema::table('user_role_users', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('role_id');
        });
        Schema::table('user_shares', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('shareable_id');
            $table->index('shareable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
