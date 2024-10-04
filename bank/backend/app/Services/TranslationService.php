<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    public function translate(string $fromLanguage, string $toLanguage, string $fromText): string
    {
        if ($toLanguage == $fromLanguage) {
            return $fromText;
        }

        $hash = md5($fromLanguage . '_' . $toLanguage . '_' . $fromText);

        $translation = Translation::where('hash', '=', $hash)
            ->where('from_language', '=', $fromLanguage)
            ->where('to_language', '=', $toLanguage)
            ->first();

        if (!is_null($translation)) {
            $toText = $translation->to_text;
        } else {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'DeepL-Auth-Key ' . env('DEEPL_API_KEY'),
                ])
                    ->asJson()
                    ->post('https://api-free.deepl.com/v2/translate', [
                        'source_lang' => $fromLanguage,
                        'target_lang' => $toLanguage,
                        'text' => [$fromText]
                    ]);

                $toText = $response->json()['translations'][0]['text'];

                Translation::create([
                    'hash' => $hash,
                    'from_language' => $fromLanguage,
                    'to_language' => $toLanguage,
                    'from_text' => $fromText,
                    'to_text' => $toText,
                ]);
            } catch (\Exception $exception) {
                Log::error('Translation Service is down', $exception->getTrace());
                return $fromText;
            }

        }

        return $toText;
    }

    public function translateSetting(Setting $setting, string $toLanguage): Setting
    {
        if (str_starts_with($setting->name, 'TRANSLATABLE_')) {
            $setting->value = $this->translate(Language::DEFAULT_META_LANGUAGE, $toLanguage, $setting->value);
        }

        return $setting;
    }

    public function translateModel(Model $model, string $toLanguage): Model
    {
        if ($model instanceof Product) {
            $model->name = $this->translate(Language::DEFAULT_META_LANGUAGE, $toLanguage, $model->name);
            $model->description = $this->translate(Language::DEFAULT_META_LANGUAGE, $toLanguage, $model->description);
        } else if ($model instanceof ProductCategory) {
            $model->name = $this->translate(Language::DEFAULT_META_LANGUAGE, $toLanguage, $model->name);
        }

        return $model;
    }
}
