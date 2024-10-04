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
        Schema::table('albums', function (Blueprint $table) {
            $table->unsignedBigInteger('resource_album_id')->nullable();
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->unsignedBigInteger('download_file_id')->nullable();
            $table->json('meta_json')->nullable();
        });

        Schema::create('resource_albums', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->string('resource_album_url');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('thumbnail_file_id')->nullable();
            $table->unsignedBigInteger('download_file_id')->nullable();
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });

        Schema::create('resource_album_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_album_id');
            $table->unsignedBigInteger('file_id');
        });

        Schema::create('resource_album_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_album_id');
            $table->unsignedBigInteger('resource_tag_id');
        });

        Schema::create('resource_album_actors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_album_id');
            $table->unsignedBigInteger('resource_actor_id');
        });

        Schema::create('resource_album_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_album_id');
            $table->unsignedBigInteger('resource_category_id');
        });

        Schema::create('album_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resource_id');
            $table->text('resource_album_url');
            $table->string('status');
            $table->json('response')->nullable();
            $table->text('errors')->nullable();
            $table->unsignedBigInteger('album_id')->nullable();
            $table->unsignedBigInteger('resource_album_id')->nullable();
            $table->unsignedBigInteger('media_queue_id')->nullable();
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
    }
};
