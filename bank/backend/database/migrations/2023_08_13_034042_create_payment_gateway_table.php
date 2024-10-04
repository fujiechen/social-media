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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('public');
            $table->string('secret');
            $table->string('webhook_secret');
            $table->timestamps();
        });

        Schema::create('user_order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_order_id');
            $table->unsignedBigInteger('payment_gateway_id');
            $table->json('request')->nullable()->comment('stripe calls will be empty');
            $table->string('action');
            $table->integer('amount');
            $table->string('stripe_intent_id')->nullable();
            $table->string('stripe_intent_client_secret')->nullable();
            $table->string('status');
            $table->json('response');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateway');
    }
};
