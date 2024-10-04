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
            $table->index('name');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('medias', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('name');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname', 255)->change();
            $table->index('nickname');
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
