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

                $grid->quickSearch(['id', 'name', 'bucket_file_path']);

                $grid->column('id')->sortable();
                $grid->column('name')->width('30%')->sortable();
                $grid->column('url')->link()->width('15%');
                $grid->column('created_at');
            });
    }
}
