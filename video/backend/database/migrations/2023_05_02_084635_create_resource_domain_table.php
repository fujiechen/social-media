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
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('video_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->text('resource_video_url');
            $table->string('status');
            $table->json('response')->nullable();
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('video_id')->nullable();
            $table->unsignedBigInteger('resource_video_id')->nullable();
            $table->unsignedBigInteger('media_queue_id')->nullable();
            $table->timestamps();
        });

        Schema::create('resource_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('resource_video_url');
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->unsignedBigInteger('file_id')->nullable();
            $table->unsignedBigInteger('preview_file_id')->nullable();
            $table->timestamps();
        });

        Schema::create('resource_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('name');
            $table->bigInteger('tag_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('resource_video_tags', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('resource_video_id')->unsigned();
            $table->bigInteger('resource_tag_id')->unsigned();
        });

        Schema::create('resource_actors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('name');
            $table->string('country');
            $table->bigInteger('actor_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('resource_video_actors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('resource_video_id')->unsigned();
            $table->bigInteger('resource_actor_id')->unsigned();
        });

        Schema::create('resource_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('name');
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::create('resource_video_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('resource_video_id')->unsigned();
            $table->bigInteger('resource_category_id')->unsigned();
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
