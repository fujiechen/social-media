<?php

namespace App\Transformers;

use App\Models\App;
use League\Fractal\TransformerAbstract;

class AppTransformer extends TransformerAbstract
{
    private AppCategoryTransformer $appCategoryTransformer;
    private FileTransformer $fileTransformer;

    public function __construct(AppCategoryTransformer $appCategoryTransformer, FileTransformer $fileTransformer) {
        $this->fileTransformer = $fileTransformer;
        $this->appCategoryTransformer = $appCategoryTransformer;
    }

    public function transform(App $app): array
    {
        return [
            'id' => $app->id,
            'name' => $app->name,
            'description' => $app->description,
            'url' => $app->url,
            'app_category' => $this->appCategoryTransformer->transform($app->appCategory),
            'icon_file' => $this->fileTransformer->transform($app->iconFile),
        ];
    }
}
