<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class UpdateMediaReadyableStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:update-media-readyable';

    protected $description = 'update media to readyable if all conditions matched';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        $logger->info('started running ' . $this->signature . ' ... ');

        $medias = Media::query();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $medias->chunk(100, function ($chunk)  use ($mediaService) {
            /**
             * @var Media $media
             */
            foreach ($chunk as $media) {
                if ($mediaService->isMediaReadyable($media)) {
                    $media->readyable = true;
                } else {
                    $media->readyable = false;
                }
                $media->save();
            }
        });

        $logger->info('completed running ' . $this->signature . ' ... ');
    }
}
