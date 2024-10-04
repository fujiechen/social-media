<?php

namespace App\Admin\Components\Tables;

use App\Models\Category;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class CategoryTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Category::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('name')->sortable()->width('50%');
                $grid->column('created_at');
            });
    }
}
