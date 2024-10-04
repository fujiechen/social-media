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

        Schema::create('playlist_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->text('resource_playlist_url');
            $table->string('status');
            $table->json('response')->nullable();
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('media_queue_id')->nullable();
            $table->timestamps();
        });

        Schema::table('video_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('playlist_queue_id')->nullable();
            $table->json('prefill_json')->nullable();
        });

        Schema::table('album_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('playlist_queue_id')->nullable();
        });

        Schema::table('media_queues', function (Blueprint $table) {
            $table->unsignedBigInteger('media_id')->nullable();
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
