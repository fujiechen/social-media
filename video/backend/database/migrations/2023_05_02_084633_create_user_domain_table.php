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

        Schema::create('user_shares', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->text('shareable_type')->comment('media,publisher,others');
            $table->bigInteger('shareable_id')->unsigned()->nullable();
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('user_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sub_user_id');
            $table->unsignedBigInteger('level')->comment('parent user whose level is 1');
            $table->unsignedBigInteger('user_share_id');
            $table->timestamps();
        });

        Schema::create('user_subscribers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('subscriber_user_id')->unsigned();
            $table->timestamps();
        });


        Role::query()->create([
            'id' => Role::ROLE_ADMINISTRATOR_ID,
            'name' => Role::ROLE_ADMINISTRATOR_NAME,
            'slug' => Role::ROLE_ADMINISTRATOR_SLUG,
        ]);
        Role::query()->create([
            'id' => Role::ROLE_VISITOR_ID,
            'name' => Role::ROLE_VISITOR_NAME,
            'slug' => Role::ROLE_VISITOR_SLUG,
        ]);
        Role::query()->create([
            'id' => Role::ROLE_USER_ID,
            'name' => Role::ROLE_USER_NAME,
            'slug' => Role::ROLE_USER_SLUG,
        ]);
        Role::query()->create([
            'id' => Role::ROLE_MEMBERSHIP_ID,
            'name' => Role::ROLE_MEMBERSHIP_NAME,
            'slug' => Role::ROLE_MEMBERSHIP_SLUG,
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
