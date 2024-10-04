<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAdminMenuTable extends Migration
{
    public function getConnection()
    {
        return $this->config('database.connection') ?: config('database.default');
    }

    public function config($key)
    {
        return config('admin.'.$key);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::table($this->config('database.menu_table'), function (Blueprint $table) {
            $table->tinyInteger('show')->default(1)->after('uri');
            $table->string('extension', 50)->default('')->after('uri');
        });
        DB::statement('SET SESSION sql_require_primary_key=1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->config('database.menu_table'), function (Blueprint $table) {
            $table->dropColumn('show');
            $table->dropColumn('extension');
        });
    }
}
