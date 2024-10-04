<?php

use App\Models\Role;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('access_token')->nullable();
            $table->bigInteger('access_token_expired_at')->nullable();
            $table->text('username');
            $table->text('password');
            $table->text('nickname')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->bigInteger('avatar_file_id')->nullable();
            $table->unsignedBigInteger('user_share_id')->nullable();
            $table->timestamps();
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
