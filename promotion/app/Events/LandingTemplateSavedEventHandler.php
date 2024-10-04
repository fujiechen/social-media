<?php

namespace App\Events;

use App\Models\LandingTemplate;
use App\Services\LandingTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;

class LandingTemplateSavedEventHandler implements ShouldQueue
{

    private LandingTemplateService $landingTemplateService;

    public function __construct(LandingTemplateService $landingTemplateService) {
        $this->landingTemplateService = $landingTemplateService;
    }


    public function handle(LandingTemplateSavedEvent $event): void {
        $landingTemplate = $event->landingTemplate;
        $status = $landingTemplate->status;

        if ($status == LandingTemplate::STATUS_ACTIVE && empty($landingTemplate->landingUrl)) {
            $this->landingTemplateService->createLandingUrl($event->landingTemplate->id);
        }
    }
}
