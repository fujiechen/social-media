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
        Schema::table('user_role_users', function (Blueprint $table) {
            $table->id();
        });
        Schema::table('user_supports', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('user_transactions', function (Blueprint $table) {
            $table->index('user_account_id');
            $table->index('user_order_id');
            $table->index('user_product_return_id');
        });
        Schema::table('user_withdraw_accounts', function (Blueprint $table) {
            $table->index('user_id');
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
