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
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('valid_util_at');
        });

        Schema::create('category_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->boolean('vpn_server_synced')->default(false);
            $table->timestamp('valid_until_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('server_users', function (Blueprint $table) {
            $table->dropColumn('valid_until_at');
            $table->unsignedBigInteger('vpn_file_id')->nullable();
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
