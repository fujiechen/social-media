<?php

namespace App\Admin\Components\Tables;

use App\Models\File;
use App\Services\FileService;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class PrivateFileTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(File::query()
            ->where('bucket_type', '=', File::TYPE_PRIVATE_BUCKET),
            function (Grid $grid) {
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}
