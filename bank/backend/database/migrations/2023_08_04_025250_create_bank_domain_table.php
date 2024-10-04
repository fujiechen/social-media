<?php

use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::create('users', function (Blueprint $table) {
            $table->id()->startingValue(100);
            $table->string('username')->unique();
            $table->string('nickname');
            $table->string('email');
            $table->string('password');
            $table->string('phone')->nullable();
            $table->string('language')->default('en');
            $table->string('whatsapp')->nullable();
            $table->string('telegram')->nullable();
            $table->string('facebook')->nullable();
            $table->unsignedBigInteger('user_agent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('symbol');
        });

        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency_id');
            $table->string('to_currency_id');
            $table->float('rate', 10, 2)->comment('cents');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('value');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('currency_id');
            $table->integer('product_category_id');
            $table->string('title');
            $table->string('name');
            $table->integer('estimate_rate')->comment('one ten thousandth');
            $table->text('description');
            $table->bigInteger('start_amount')->comment('cents');
            $table->integer('stock');
            $table->integer('freeze_days');
            $table->string('trend')->nullable();
            $table->boolean('is_recommend')->default(false);
            $table->string('fund_fact_url')->nullable();
            $table->string('prospectus_url')->nullable();
            $table->string('fund_assets')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id');
            $table->integer('rate')->comment('one ten thousandth');
            $table->float('value', 16, 2);
            $table->timestamps();
        });

        Schema::create('user_orders', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('purchase, deposit, withdraw_account, withdraw_cash');
            $table->integer('user_account_id');
            $table->integer('product_id')->nullable();
            $table->integer('to_user_account_id')->nullable();
            $table->integer('to_user_withdraw_account_id')->nullable();
            $table->integer('to_user_address_id')->nullable();
            $table->bigInteger('amount')->comment('in cents');
            $table->bigInteger('start_amount')->nullable()->comment('in cents');;
            $table->integer('freeze_days')->nullable();
            $table->string('status')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('user_products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_order_id');
            $table->boolean('is_active')->comment('active = earning, inactive = finished');
            $table->bigInteger('total_market_value')->comment('in cents for earning');
            $table->bigInteger('total_book_cost')->comment('in cents for earning');
            $table->bigInteger('final_market_value')->comment('in cents for finished');
            $table->bigInteger('final_book_cost')->comment('in cents for finished');
            $table->timestamps();
        });

        Schema::create('user_product_returns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_product_id');
            $table->integer('product_rate_id')->nullable()->comment('when product is done, no rate');
            $table->bigInteger('market_value')->comment('in cents');
            $table->bigInteger('book_cost')->comment('in cents');
            $table->string('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('income|expense');
            $table->integer('user_account_id');
            $table->integer('user_order_id')->nullable();
            $table->integer('user_product_return_id')->nullable();
            $table->bigInteger('amount')->comment('in cents');;
            $table->bigInteger('balance')->comment('in cents');;
            $table->string('status')->comment('describe source');
            $table->string('comment');
            $table->timestamps();
        });

        Schema::create('user_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('account_number');
            $table->integer('currency_id');
            $table->bigInteger('product_balance')->comment('in cents');;
            $table->bigInteger('balance')->comment('cash balance in cents');;
            $table->timestamps();
        });

        Schema::create('user_supports', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('comment');
            $table->timestamps();
        });

        Schema::create('user_withdraw_accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('name');
            $table->string('phone');
            $table->string('account_number');
            $table->string('bank_name');
            $table->string('bank_address');
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->string('country');
            $table->string('province');
            $table->string('city');
            $table->string('zip');
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('from_language');
            $table->text('from_text');
            $table->string('to_language');
            $table->text('to_text');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('user_agents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('code');
            $table->timestamps();
        });
        DB::statement('SET SESSION sql_require_primary_key=1');
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
