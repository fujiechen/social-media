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
        Schema::create('series_queues', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->string('status');
            $table->text('errors')->nullable();
            $table->timestamps();
        });

        Schema::table('video_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('series_queue_id')->nullable();
        });

        Schema::table('album_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('series_queue_id')->nullable();
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
