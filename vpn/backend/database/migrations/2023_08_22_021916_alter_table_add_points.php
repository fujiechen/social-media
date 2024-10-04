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
        Schema::create('user_payouts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('earning|commission');
            $table->string('status')->comment('pending|completed');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_product_id')->nullable();
            $table->string('currency_name');
            $table->unsignedBigInteger('amount_cents');
            $table->string('comment');
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('currency_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_points');
            $table->string('currency_name');
        });

        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('unit_points');
            $table->string('currency_name');
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
