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
        Schema::create('app_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_category_id');
            $table->unsignedBigInteger('icon_file_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('url');
            $table->boolean('is_hot')->default(false);
            $table->timestamps();
        });

        Schema::create('tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('os');
            $table->string('name');
            $table->mediumText('content');
            $table->timestamps();
        });

        Schema::create('tutorial_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('tutorial_id');
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
