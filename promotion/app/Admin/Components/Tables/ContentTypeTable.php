<?php

namespace App\Admin\Components\Tables;

use App\Models\ContentType;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;

class ContentTypeTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(ContentType::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name')->width('10%');
                $grid->column('description', 'Description');
                $grid->column('file_urls', 'Files')->display(function($item) {
                    $urls = '';
                    foreach ($item as $url) {
                        $urls .= '<a href="' . Str::replace('\/', '/', $url) . '" target=_blank>' . Str::replace('\/', '/', $url) . '</a><br/>';
                    }
                    return  $urls;
                });
            });
    }
}
