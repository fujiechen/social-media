<?php

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
        Schema::table('medias', function (Blueprint $table) {
            $table->unsignedBigInteger('media_permission_id')->nullable();
        });

        Schema::create('media_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_id')->index();
            $table->string('permission')->index();
            $table->unique(['media_id', 'permission']);
        });

        $batchSize = 100;
        DB::transaction(function () use ($batchSize) {
            DB::table('medias')
                ->whereNotNull('media_permission')
                ->orderBy('id')
                ->chunk($batchSize, function ($medias) {
                    foreach ($medias as $media) {
                        // Insert into media_permissions
                        $mediaPermissionId = DB::table('media_permissions')->insertGetId([
                            'media_id' => $media->id,
                            'permission' => $media->media_permission,
                        ]);

                        // Update medias table with the new media_permission_id
                        DB::table('medias')->where('id', $media->id)->update([
                            'media_permission_id' => $mediaPermissionId,
                        ]);
                    }
                });
        });

        Schema::table('medias', function (Blueprint $table) {
            $table->unsignedBigInteger('media_permission_id')->nullable(false)->change();
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
