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
        Schema::create('media_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('media_type');
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('role_ids');
            $table->string('status');
            $table->text('errors')->nullable();
            $table->timestamps();
        });

        Schema::create('medias', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('mediaable_type');
            $table->bigInteger('mediaable_id')->unsigned();
            $table->string('media_permission');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('media_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('media_id')->unsigned();
            $table->text('comment');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('media_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();
        });

        Schema::create('media_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('media_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id');
            $table->timestamps();
        });

        Schema::create('media_roles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id')->unsigned();
            $table->bigInteger('media_id')->unsigned();
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->comment('upload, cloud, or resource');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->unsignedBigInteger('video_file_id')->nullable();
            $table->unsignedBigInteger('preview_file_id')->nullable();
            $table->unsignedBigInteger('resource_video_id')->nullable();
            $table->timestamps();
        });

        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->comment('upload, cloud, or resource');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->timestamps();
        });

        Schema::create('series_videos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('series_id')->unsigned();
            $table->bigInteger('video_id')->unsigned();
        });

        Schema::create('albums', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('album_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('album_id')->unsigned();
            $table->bigInteger('file_id')->unsigned();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('upload_path')->nullable()->comment('upload path');
            $table->string('bucket_type')->comment('private or public');
            $table->string('bucket_name')->nullable();
            $table->string('bucket_file_name')->nullable();
            $table->string('bucket_file_path')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('video_tags', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('video_id')->unsigned();
            $table->bigInteger('tag_id')->unsigned();
        });

        Schema::create('actors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country')->nullable();
            $table->bigInteger('avatar_file_id')->nullable();
            $table->timestamps();
        });

        Schema::create('video_actors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('video_id')->unsigned();
            $table->bigInteger('actor_id')->unsigned();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('avatar_file_id')->unsigned();
            $table->timestamps();
        });

        Schema::create('video_categories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('video_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
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
