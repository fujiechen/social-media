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
        Schema::create('order_notifiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description');
            $table->string('access_token');
            $table->string('notifier_url');
            $table->integer('max_retry_times');
            $table->timestamps();
        });

        Schema::create('user_order_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_order_id');
            $table->unsignedBigInteger('order_notifier_id');
            $table->json('payload');
            $table->string('status')->comment('success or fail');
            $table->timestamps();
        });

        Schema::table('user_orders', function (Blueprint $table) {
            $table->json('meta_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
