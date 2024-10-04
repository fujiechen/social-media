<?php

namespace App\Console\Commands;

use App\Services\MediaRecommendationService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class ProcessMediaRecommendation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:process-media-recommendation';

    protected $description = 'Process Media Recommendations';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        /**
         * @var MediaRecommendationService $mediaRecommendationService
         */
        $mediaRecommendationService = app(MediaRecommendationService::class);

        $logger->info('running recommendations ... ');
        $mediaRecommendationService->createMediaRecommendationForVisitor();
        $mediaRecommendationService->createMediaRecommendationForRegistration();
        $mediaRecommendationService->createMediaRecommendationForMembership();
        $logger->info('completed running recommendations ... ');
    }
}
