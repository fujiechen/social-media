<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    protected function getLanguage(Request $request): string
    {
        $languageFromRequest = $request->get('language', null);

        $languageFromUser = Auth::guard('api')->user()?->language;

        if (!is_null($languageFromRequest)) {
            $language = $languageFromRequest;
        } else if (!is_null($languageFromUser)) {
            $language = $languageFromUser;
        } else {
            $language = Language::DEFAULT_LANGUAGE;
        }
        return $language;
    }
}
