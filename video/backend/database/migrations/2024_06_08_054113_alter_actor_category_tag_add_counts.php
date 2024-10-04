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
        Schema::table('actors', function (Blueprint $table) {
            $table->unsignedBigInteger('active_media_videos_count')->default(0);
            $table->unsignedBigInteger('active_media_series_count')->default(0);
            $table->unsignedBigInteger('active_media_albums_count')->default(0);
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->unsignedBigInteger('active_media_videos_count')->default(0);
            $table->unsignedBigInteger('active_media_series_count')->default(0);
            $table->unsignedBigInteger('active_media_albums_count')->default(0);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('active_media_videos_count')->default(0);
            $table->unsignedBigInteger('active_media_series_count')->default(0);
            $table->unsignedBigInteger('active_media_albums_count')->default(0);
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
