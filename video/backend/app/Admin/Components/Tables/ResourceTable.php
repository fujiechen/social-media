<?php

namespace App\Admin\Components\Tables;

use App\Models\Resource;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ResourceTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Resource::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id');
                $grid->column('name')->width('30%');
                $grid->column('created_at');
            });
    }
}
