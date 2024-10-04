<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement('SET SESSION sql_require_primary_key=0');
        Schema::table('user_role_users', function (Blueprint $table) {
            $table->dropColumn('updated_at');
            $table->dropColumn('created_at');
        });

        Schema::table('user_role_users', function (Blueprint $table) {
            $table->timestamp('updated_at');
            $table->timestamp('created_at');
            $table->timestamp('valid_util_at')->nullable();
        });

        Role::query()->create([
            'id' => Role::ROLE_ADMINISTRATOR_ID,
            'name' => Role::ROLE_ADMINISTRATOR_NAME,
            'slug' => Role::ROLE_ADMINISTRATOR_SLUG,
        ]);

        Role::query()->create([
            'id' => Role::ROLE_USER_ID,
            'name' => Role::ROLE_USER_NAME,
            'slug' => Role::ROLE_USER_SLUG,
        ]);

        Role::query()->create([
            'id' => Role::ROLE_AGENT_ID,
            'name' => Role::ROLE_AGENT_NAME,
            'slug' => Role::ROLE_AGENT_SLUG,
        ]);
        DB::statement('SET SESSION sql_require_primary_key=1');
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
