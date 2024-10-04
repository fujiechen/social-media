<?php

namespace App\Admin\Components\Tables;

use App\Models\Album;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class AlbumTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Album::query(),
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
