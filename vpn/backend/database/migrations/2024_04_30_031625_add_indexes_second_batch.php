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
        Schema::table('server_users', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('server_id');
        });
        Schema::table('servers', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('ip');
        });
        Schema::table('tutorials', function (Blueprint $table) {
            $table->index('os');
        });
        Schema::table('metas', function (Blueprint $table) {
            $table->index('key');
        });
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->index('level');
            $table->index('user_share_id');
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
