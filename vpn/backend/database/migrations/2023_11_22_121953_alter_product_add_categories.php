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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('plan');
            $table->unsignedBigInteger('category_id');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->text('name');
            $table->text('description')->nullable();
            $table->bigInteger('thumbnail_file_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
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
