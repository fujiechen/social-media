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
        Schema::create('products', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->text('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('user product, null is platform');
            $table->unsignedBigInteger('publisher_user_id')->nullable()->comment('user who subscribe the publisher');
            $table->unsignedBigInteger('role_id')->nullable()->comment('user role upgrade');
            $table->unsignedBigInteger('media_id')->nullable()->comment('media id');
            $table->bigInteger('thumbnail_file_id')->unsigned()->nullable();
            $table->bigInteger('unit_cents')->nullable();
            $table->string('frequency')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('file_id');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('status');
            $table->bigInteger('total_cents')->unsigned()->nullable();
            $table->bigInteger('total_points')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('order_products', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->bigInteger('order_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->json('product_json')->comment('clone product info');
            $table->bigInteger('unit_cents')->nullable();
            $table->bigInteger('unit_points')->nullable();
            $table->bigInteger('qty');
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
