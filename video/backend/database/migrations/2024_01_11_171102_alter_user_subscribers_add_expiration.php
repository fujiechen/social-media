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
        Schema::create('user_followings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_user_id');
            $table->unsignedBigInteger('following_user_id')->unsigned();
            $table->timestamp('valid_until_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::drop('user_subscribers');
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
