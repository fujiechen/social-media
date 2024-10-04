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
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->index(['from_currency_id', 'to_currency_id']);
        });
        Schema::table('product_rates', function (Blueprint $table) {
            $table->index('product_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('currency_id');
            $table->index('product_category_id');
            $table->index('is_recommend');
        });
        Schema::table('user_accounts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('account_number');
            $table->index('currency_id');
        });
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('user_agents', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('user_order_payments', function (Blueprint $table) {
            $table->index('user_order_id');
            $table->index('payment_gateway_id');
        });
        Schema::table('user_orders', function (Blueprint $table) {
            $table->index('user_account_id');
            $table->index('product_id');
            $table->index('to_user_account_id');
            $table->index('to_user_withdraw_account_id');
        });
        Schema::table('user_product_returns', function (Blueprint $table) {
            $table->index('user_product_id');
        });
        Schema::table('user_products', function (Blueprint $table) {
            $table->index('user_order_id');
            $table->index('is_active');
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
