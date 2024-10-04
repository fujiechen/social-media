<?php

use App\Models\Contact;
use App\Models\LandingDomain;
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
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('content_type_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->unsignedBigInteger('file_id');
            $table->timestamps();
        });

        Schema::create('redirect_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('redirect_types')->insert([
            [
                'name' => 'QR Redirect',
                'description' => 'Scan landing page url QR to redirect',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Link Redirect',
                'description' => 'Url button to redirect',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('contact')->unique();
            $table->string('description');
            $table->string('admin_url');
            $table->string('admin_username');
            $table->string('admin_password');
            $table->timestamps();
        });

        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('admin_url');
            $table->string('contact_type');
            $table->timestamps();
        });

        DB::table('account_types')->insert([
            [
                'name' => '小红书',
                'description' => '网页版登录无法同时管理多账户和设置密码, 登录时无法用国外手机登录',
                'admin_url' => 'https://www.xiaohongshu.com/explore',
                'contact_type' => Contact::TYPE_PHONE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '新浪微博',
                'description' => '网页版无法同时管理多账户和设置密码, 登录时可以用国外手机登录',
                'admin_url' => 'https://passport.weibo.com/sso/signin?entry=miniblog&source=miniblog&url=',
                'contact_type' => Contact::TYPE_PHONE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '抖音',
                'description' => '',
                'admin_url' => 'https://www.douyin.com/',
                'contact_type' => Contact::TYPE_PHONE,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('instruction')->nullable();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('account_type_id');
            $table->string('account_no')->nullable();
            $table->string('nickname')->nullable();
            $table->string('account_url')->nullable();
            $table->string('description')->nullable();
            $table->string('admin_username')->nullable();
            $table->string('admin_password')->nullable();
            $table->string('profile_description')->nullable();
            $table->string('profile_avatar_file_id')->nullable();
            $table->string('profile_background_file_id')->nullable();
            $table->unsignedBigInteger('landing_template_id')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('instruction')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('post_url')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('content_type_id')->nullable();
            $table->unsignedBigInteger('landing_template_id');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('post_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('file_id');
        });

        Schema::create('landing_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->mediumText('landing_html');
            $table->unsignedBigInteger('redirect_type_id');
            $table->unsignedBigInteger('banner_file_id');
            $table->unsignedBigInteger('landing_domain_id');
            $table->unsignedBigInteger('target_url_id');
            $table->string('status');
            $table->string('landing_url');
            $table->timestamps();
        });

        Schema::create('landing_domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('access_key');
            $table->string('secret');
            $table->string('region');
            $table->string('bucket');
            $table->string('endpoint_url');
            $table->string('cdn_url')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        DB::table('landing_domains')->insert([
            [
                'name' => 'Digital Ocean (Test)',
                'description' => 'Digital Ocean Storage for test purpose, NOT use for public',
                'access_key' => 'DO00BRZLGY6FVFTNKYCW',
                'secret' => 'kwZJnMGEFjVG2ZtiODYYGgNWJah0feg5VCVNyrOPgNE',
                'region' => 'sgp1',
                'bucket' => 'varc.promotion',
                'endpoint_url' => 'https://varc-public.sgp1.digitaloceanspaces.com',
                'cdn_url' => 'https://varc-public.sgp1.digitaloceanspaces.com',
                'status' => LandingDomain::STATUS_ACTIVE,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('target_urls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->unsignedBigInteger('qr_file_id')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('landings', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('signature')->index();
            $table->unsignedBigInteger('landing_template_id');
            $table->unsignedBigInteger('post_id')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->string('ip');
            $table->string('country')->nullable();
            $table->boolean('redirect');
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
