<?php

namespace App\Events;

use App\Models\LandingTemplate;
use Illuminate\Queue\SerializesModels;

class LandingTemplateSavedEvent
{
    use SerializesModels;

    public LandingTemplate $landingTemplate;

    public function __construct(LandingTemplate $landingTemplate) {
        $this->landingTemplate = $landingTemplate;
    }
}
