<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class ActivateMedias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:activate-medias';

    protected $description = 'Activate medias from status ready daily';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        $logger->info('started running video:activate-medias ... ');
        $medias = Media::query()
            ->where('status','=', Media::STATUS_READY)
            ->limit(10)
            ->get();

        /**
         * @var Media $media
         */
        foreach ($medias as $media) {
            $logger->info('activate media ' . $media->id);
            $media->status = Media::STATUS_ACTIVE;
            $media->save();
        }

        $logger->info('completed running video:activate-medias ... ');
    }
}
