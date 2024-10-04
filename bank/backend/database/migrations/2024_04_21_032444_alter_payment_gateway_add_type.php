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
        Schema::table('payment_gateways', function (Blueprint $table) {
            $table->string('payment_gateway_type');
            $table->json('payment_methods');
            $table->string('public')->nullable()->change();
            $table->string('webhook_secret')->nullable()->change();
            $table->string('webhook_url')->nullable();
            $table->string('endpoint_url')->nullable();
            $table->tinyInteger('is_active')->default(1);
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
