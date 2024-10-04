<?php

namespace App\Admin\Components\Tables;

use App\Models\Actor;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ActorTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Actor::query(),
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
