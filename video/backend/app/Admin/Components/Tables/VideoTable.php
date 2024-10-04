<?php

namespace App\Admin\Components\Tables;

use App\Models\Video;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class VideoTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Video::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('name')->sortable();
                $grid->column('created_at');
            });
    }
}
