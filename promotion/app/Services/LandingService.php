<?php

namespace App\Services;

use App\Dtos\LandingDto;
use App\Models\Landing;

class LandingService
{
    public function createLanding(LandingDto $dto): Landing {
        return Landing::create([
            'url' => $dto->url,
            'signature' => $dto->signature,
            'landing_template_id' => $dto->landingTemplateId,
            'post_id' => $dto->postId,
            'account_id' => $dto->accountId,
            'ip' => $dto->ip,
            'redirect' => $dto->redirect
        ]);
    }
}
