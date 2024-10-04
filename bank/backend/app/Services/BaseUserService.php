<?php

namespace App\Services;

use App\Models\User;
use App\Models\Language;

class BaseUserService
{
    private TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    protected function t($userId, $message): string {
        $user = User::find($userId);
        return $this->translationService->translate(Language::DEFAULT_LANGUAGE, $user->language, $message);
    }
}
