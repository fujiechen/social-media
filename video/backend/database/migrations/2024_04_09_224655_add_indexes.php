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
        Schema::table('album_actors', function (Blueprint $table) {
            $table->index(['album_id', 'actor_id']);
        });
        Schema::table('album_categories', function (Blueprint $table) {
            $table->index(['album_id', 'category_id']);
        });
        Schema::table('album_files', function (Blueprint $table) {
            $table->index(['album_id', 'file_id']);
        });
        Schema::table('album_queues', function (Blueprint $table) {
            $table->index('album_id');
            $table->index('resource_id');
            $table->index('media_queue_id');
        });
        Schema::table('album_tags', function (Blueprint $table) {
            $table->index(['album_id', 'tag_id']);
        });
        Schema::table('media_actors', function (Blueprint $table) {
            $table->index(['media_id', 'actor_id']);
        });
        Schema::table('media_categories', function (Blueprint $table) {
            $table->index(['media_id', 'category_id']);
        });
        Schema::table('media_comments', function (Blueprint $table) {
            $table->index('media_id');
            $table->index('user_id');
        });
        Schema::table('media_favorites', function (Blueprint $table) {
            $table->index('media_id');
            $table->index('user_id');
        });
        Schema::table('media_histories', function (Blueprint $table) {
            $table->index('media_id');
            $table->index('user_id');
        });
        Schema::table('media_likes', function (Blueprint $table) {
            $table->index('media_id');
            $table->index('user_id');
            $table->index('type');
        });
        Schema::table('media_roles', function (Blueprint $table) {
            $table->index('role_id');
        });
        Schema::table('media_tags', function (Blueprint $table) {
            $table->index('media_id');
            $table->index('tag_id');
        });
        Schema::table('medias', function (Blueprint $table) {
            $table->index(['mediaable_type', 'mediaable_id']);
        });
        Schema::table('order_products', function (Blueprint $table) {
            $table->index(['order_id', 'product_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['user_payout_id', 'order_id']);
        });
        Schema::table('product_images', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('file_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('media_id');
        });
        Schema::table('series_albums', function (Blueprint $table) {
            $table->index('series_id');
            $table->index('album_id');
        });
        Schema::table('series_videos', function (Blueprint $table) {
            $table->index('series_id');
            $table->index('video_id');
        });
        Schema::table('user_followings', function (Blueprint $table) {
            $table->index('publisher_user_id');
            $table->index('following_user_id');
        });
        Schema::table('user_payouts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('order_product_id');
        });
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('sub_user_id');
            $table->index('user_share_id');
        });
        Schema::table('user_searches', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('user_shares', function (Blueprint $table) {
            $table->index('user_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 255)->change();
            $table->string('email', 255)->change();
            $table->index('username');
        });
        Schema::table('video_actors', function (Blueprint $table) {
            $table->index('video_id');
            $table->index('actor_id');
        });
        Schema::table('video_categories', function (Blueprint $table) {
            $table->index('video_id');
            $table->index('category_id');
        });
        Schema::table('video_tags', function (Blueprint $table) {
            $table->index('video_id');
            $table->index('tag_id');
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->index('type');
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
