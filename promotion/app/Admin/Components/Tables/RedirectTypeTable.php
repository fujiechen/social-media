<?php

namespace App\Admin\Components\Tables;

use App\Models\RedirectType;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class RedirectTypeTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(RedirectType::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['name']);

                $grid->column('id', 'Id');
                $grid->column('name', 'Name')->width('30%');
                $grid->column('description', 'Description');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}
